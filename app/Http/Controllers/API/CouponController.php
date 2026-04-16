<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessUnit;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends BaseController
{
    public function index(Request $request)
    {

        $zipCode = $request->header('zip-code');

        if (!$zipCode) {
            return $this->sendError("Favor informar seu CEP.", [], 403);
        }

        $businessUnit = BusinessUnit::where('zip_code_start', "<=", $zipCode)
            ->where('zip_code_end', '>=', $zipCode)
            ->first();

        $coupons = Coupon::query()
            ->when($request->has('enabled') && $request->enabled, function ($query) {
                $query->where('start_at', '<=', now())
                    ->where('end_at', '>=', now())
                    ->where('is_enabled', true);
            })
            ->where(function ($query) use ($businessUnit) {
                $query->where('business_unit_id', optional($businessUnit)->id)
                    ->orWhereNull('business_unit_id');
            })

            ->get();

        return $this->sendResponse($coupons);
    }

    public function inactivate($id)
    {
        $coupon = Coupon::findOrFail($id);
        if (!$coupon->is_enabled) {
            return response('Este Cupon já encontra-se inativado.', 500);
        } else {
            $coupon->is_enabled = 0;
            $coupon->save();
            return response('Cupon inativado com sucesso.', 200);
        }
    }
}
