<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Tenant;

class WhatsAppController extends Controller
{
    public function product(Product $product)
    {
        $product->load('tenant');
        $product->tenant->increment('wa_clicks');

        return redirect()->away($product->tenant->waLink());
    }

    public function tenant(Tenant $tenant)
    {
        $tenant->increment('wa_clicks');

        return redirect()->away($tenant->waLink());
    }
}
