<?php

namespace App\Http\Controllers;

use App\Models\Socks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SocksController extends Controller
{
    public function income(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'color' => 'required|string|max:255',
            'cottonPart' => 'required|integer|min:0|max:100',
            'quantity' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $validated = $validator->validated();
        
        $socks = Socks::updateOrCreate(
            [
                'color' => $validated['color'],
                'cottonPart' => $validated['cottonPart']
            ],
            [
                'quantity' => DB::raw('quantity + ' . $validated['quantity'])
            ]
        );
        $socks->refresh();

        return response()->json($socks, 200);
    }

    public function outcome(Request $request){
        $validator = Validator::make($request->all(), [
            'color' => 'required|string|max:255',
            'cottonPart' => 'required|integer|min:0|max:100',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $validated = $validator->validated();
        $socks = Socks::where([
            'color' => $request->color,
            'cottonPart' => $request->cottonPart
        ])->first();

        if (!$socks) {
            return response()->json(['message' => 'Носки не найдены'], 404);
        }
        $socks->quantity = $socks->quantity - $request->quantity;
        if ($socks->quantity <= 0) {
            $socks->delete();
            return response()->json(['message' => 'Все носки удалены'], 200);
        }
        $socks->save();

        return response()->json(['message' => 'Удалено ' .$request->quantity . ' носок' , 'осталось' => $socks->quantity], 200);
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'color' => 'required|string',
            'operation' => 'required|in:moreThan,lessThan,equal',
            'cottonPart' => 'required|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            $query = Socks::where('color', $request->color);

            switch ($request->operation) {
                case 'moreThan':
                    $query->where('cottonPart', '>', $request->cottonPart);
                    break;
                case 'lessThan':
                    $query->where('cottonPart', '<', $request->cottonPart);
                    break;
                case 'equal':
                    $query->where('cottonPart', '=', $request->cottonPart);
                    break;
            }

            $totalQuantity = $query->sum('quantity');

            return response((string)$totalQuantity, 200)
                ->header('Content-Type', 'text/plain');

        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}