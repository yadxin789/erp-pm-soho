<?php

namespace App\Core\Services;

use RuntimeException;
use ZipArchive;

/**
 * 文档正文抽取（TC-12，来自 PM 的 DocumentTextExtractorService，按本机可用扩展裁剪）。
 *
 * PM 原来用 phpoffice/phpspreadsheet 读 Excel，本仓库装的是 maatwebsite/excel（底层仍是 PhpSpreadsheet），
 * 所以 Excel 走 ZipArchive 直接读 `xl/sharedStrings.xml` + `xl/worksheets/*.xml`，不额外加依赖。
 * 任何一种格式解析失败都返回空串（上层标记「未能自动解析」），绝不让请求 500。
 */
class DocumentTextExtractor
{
    public const MAX_CHARS = 20000;

    private const MAX_BYTES = 20 * 1024 * 1024;

    public function extract(string $path, ?string $extension = null, int $maxChars = self::MAX_CHARS): string
    {
        if (! is_file($path) || filesize($path) > self::MAX_BYTES) {
            return '';
        }

        $ext = strtolower(trim((string) ($extension ?? pathinfo($path, PATHINFO_EXTENSION)), '.'));

        try {
            $text = match ($ext) {
                'txt', 'md', 'markdown', 'csv', 'json', 'xml', 'log', 'yml', 'yaml', 'ini', 'sql', 'html', 'htm'
                    => $this->plain($path),
                'pdf' => $this->pdf($path),
                'docx' => $this->ooxml($path, 'word/document.xml'),
                'pptx' => $this->pptx($path),
                'xlsx', 'xlsm' => $this->xlsx($path),
                'odt' => $this->ooxml($path, 'content.xml'),
                default => '',
            };
        } catch (\Throwable $e) {
            report($e);
            $text = '';
        }

        return mb_substr($text, 0, $maxChars);
    }

    private function plain(string $path): string
    {
        return (string) file_get_contents($path);
    }

    private function pdf(string $path): string
    {
        if (! class_exists(\Smalot\PdfParser\Parser::class)) {
            return '';
        }

        $pdf = (new \Smalot\PdfParser\Parser())->parseFile($path);

        return $pdf->getText();
    }

    /**
     * OOXML（docx / odt）：解压后把 XML 标签剥掉，段落用换行分隔。
     */
    private function ooxml(string $path, string $entry): string
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('无法打开压缩包：'.$path);
        }

        $xml = (string) $zip->getFromName($entry);
        $zip->close();

        if ($xml === '') {
            return '';
        }

        $xml = str_replace(['</w:p>', '</text:p>', '<w:br/>', '<w:tab/>'], "\n", $xml);
        $text = strip_tags($xml);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');

        return trim(preg_replace("/\n{3,}/", "\n\n", $text) ?? $text);
    }

    private function pptx(string $path): string
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('无法打开压缩包：'.$path);
        }

        $parts = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_starts_with($name, 'ppt/slides/slide') && str_ends_with($name, '.xml')) {
                $parts[$name] = (string) $zip->getFromIndex($i);
            }
        }
        $zip->close();

        natsort($parts);
        $slides = [];
        foreach ($parts as $name => $xml) {
            $xml = str_replace(['</a:p>', '<a:br/>'], "\n", $xml);
            $text = trim(html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8'));
            if ($text !== '') {
                $slides[] = '[slide '.(count($slides) + 1)."]\n".$text;
            }
        }

        return implode("\n\n", $slides);
    }

    /**
     * xlsx：共享字符串 + 各 sheet 的文本，单元格用制表符分隔。
     */
    private function xlsx(string $path): string
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('无法打开压缩包：'.$path);
        }

        $shared = [];
        $raw = (string) $zip->getFromName('xl/sharedStrings.xml');
        if ($raw !== '') {
            if (preg_match_all('/<si>.*?<\/si>/s', $raw, $m)) {
                foreach ($m[0] as $si) {
                    $shared[] = trim(html_entity_decode(strip_tags($si), ENT_QUOTES | ENT_XML1, 'UTF-8'));
                }
            }
        }

        $sheets = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_starts_with($name, 'xl/worksheets/sheet') && str_ends_with($name, '.xml')) {
                $xml = (string) $zip->getFromIndex($i);
                $rows = [];
                if (preg_match_all('/<row[^>]*>(.*?)<\/row>/s', $xml, $rm)) {
                    foreach ($rm[1] as $rowXml) {
                        $cells = [];
                        if (preg_match_all('/<c[^>]*?(t="(\w+)")?[^>]*>(.*?)<\/c>/s', $rowXml, $cm, PREG_SET_ORDER)) {
                            foreach ($cm as $cell) {
                                $type = $cell[2] ?? '';
                                $inner = $cell[3];
                                $value = '';
                                if (preg_match('/<v>(.*?)<\/v>/s', $inner, $vm)) {
                                    $value = $type === 's' ? ($shared[(int) $vm[1]] ?? '') : $vm[1];
                                } elseif (preg_match('/<is>.*?<t[^>]*>(.*?)<\/t>.*?<\/is>/s', $inner, $im)) {
                                    $value = $im[1];
                                }
                                $cells[] = trim(html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_XML1, 'UTF-8'));
                            }
                        }
                        if ($cells) {
                            $rows[] = implode("\t", $cells);
                        }
                    }
                }
                if ($rows) {
                    $sheets[] = basename($name)."\n".implode("\n", $rows);
                }
            }
        }
        $zip->close();

        return implode("\n\n", $sheets);
    }
}
