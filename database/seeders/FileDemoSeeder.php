<?php

namespace Database\Seeders;

use App\Core\Models\User;
use App\Core\Services\FileService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/**
 * TC-12 演示文件：三份中文名文件（txt / csv / docx），其中 docx 用 ZipArchive 现造，
 * 保证「中文名不乱码」「正文可抽取」两条都能在界面与脚本里验证。
 */
class FileDemoSeeder extends Seeder
{
    public function run(): void
    {
        /** @var FileService $service */
        $service = app(FileService::class);
        $admin = User::where('username', 'admin')->first();
        if (! $admin) {
            return;
        }

        $tmp = sys_get_temp_dir().DIRECTORY_SEPARATOR.'ddwl-file-demo';
        if (! is_dir($tmp)) {
            mkdir($tmp, 0777, true);
        }

        $samples = [
            [
                'name' => '供应商送货单-宁波华强电子-20261117.txt',
                'category' => 'voucher',
                'visibility' => 'module',
                'content' => "送货单号：SH-2026-1117\n供应商：宁波华强电子有限公司\n物料：0402 贴片电容 100nF\n数量：12000 只\n批次：20261115-A\n质检结论：抽检合格，允收。\n",
            ],
            [
                'name' => 'SMT产线不良率统计-2026年11月.csv',
                'category' => 'report',
                'visibility' => 'module',
                'content' => "日期,产线,投入数,不良数,不良率\n2026-11-01,SMT-1,8600,12,0.14%\n2026-11-02,SMT-2,9100,21,0.23%\n2026-11-03,SMT-1,8700,9,0.10%\n",
            ],
            [
                'name' => '研发项目接口评审纪要-智能网关V2.docx',
                'category' => 'contract',
                'visibility' => 'module',
                'content' => null,
            ],
        ];

        foreach ($samples as $i => $sample) {
            $path = $tmp.DIRECTORY_SEPARATOR.'sample-'.$i;
            if ($sample['content'] === null) {
                $this->makeDocx($path, [
                    '智能网关 V2 接口评审纪要',
                    '参与人：项目监督人、系统管理员、研发部硬件组',
                    '结论一：硬件侧确认接口时序，结构件下周到样。',
                    '结论二：T-118 排期后移两天，不影响整体里程碑。',
                ]);
                $name = $sample['name'];
            } else {
                file_put_contents($path, $sample['content']);
                $name = $sample['name'];
            }

            $upload = new UploadedFile($path, $name, null, null, true);
            try {
                $service->store($upload, $admin, 'erp', [
                    'category' => $sample['category'],
                    'visibility' => $sample['visibility'],
                ]);
            } catch (\Throwable $e) {
                // 同一批已经灌过就跳过（文件是 UUID 命名，不会重复覆盖）
            }

            @unlink($path);
        }

        @rmdir($tmp);
    }

    private function makeDocx(string $path, array $paragraphs): void
    {
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');

        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');

        $body = '';
        foreach ($paragraphs as $text) {
            $body .= '<w:p><w:r><w:t xml:space="preserve">'.htmlspecialchars($text, ENT_QUOTES | ENT_XML1, 'UTF-8').'</w:t></w:r></w:p>';
        }

        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'.$body.'</w:body></w:document>');

        $zip->close();
    }
}
