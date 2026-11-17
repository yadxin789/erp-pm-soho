<?php

namespace App\Erp\Http\Controllers\Admin;

use App\Erp\Http\Controllers\Controller;
use App\Erp\Models\MasterData\Item;
use App\Erp\Models\MasterData\Customer;
use App\Erp\Models\MasterData\Supplier;
use App\Erp\Models\MasterData\Warehouse;
use App\Erp\Models\Production\BomHeader;
use App\Erp\Models\Production\RoutingHeader;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    public function items()
    {
        $items = Item::paginate(20);
        return view('admin.items.index', compact('items'));
    }

    public function customers()
    {
        $customers = Customer::paginate(20);
        return view('admin.customers.index', compact('customers'));
    }

    public function suppliers()
    {
        $suppliers = Supplier::paginate(20);
        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function warehouses()
    {
        $warehouses = Warehouse::paginate(20);
        return view('admin.warehouses.index', compact('warehouses'));
    }

    public function boms()
    {
        $boms = BomHeader::paginate(20);
        return view('admin.boms.index', compact('boms'));
    }

    public function routings()
    {
        $routings = RoutingHeader::paginate(20);
        return view('admin.routings.index', compact('routings'));
    }

    public function routingShow(RoutingHeader $routing)
    {
        return view('admin.routings.show', compact('routing'));
    }
    
}
