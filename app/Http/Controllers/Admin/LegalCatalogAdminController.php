<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotarySetting;
use Illuminate\Http\Request;

class LegalCatalogAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('super_admin');
    }

    public function index()
    {
        $items = NotarySetting::query()
            ->whereNotIn('document_type', ['branding'])
            ->orderBy('category')
            ->orderBy('document_type')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'document_type' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/', 'unique:notary_settings,document_type'],
            'category' => ['required', 'string', 'max:120'],
            'price' => ['required', 'numeric', 'min:0'],
            'requires_court_stamp' => ['required', 'boolean'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($data['document_type'] === 'branding') {
            return response()->json(['success' => false, 'message' => 'branding is reserved.'], 422);
        }

        $item = NotarySetting::create($data);

        return response()->json([
            'success' => true,
            'data' => $item,
        ], 201);
    }

    public function update(NotarySetting $notarySetting, Request $request)
    {
        if ($notarySetting->document_type === 'branding') {
            return response()->json(['success' => false, 'message' => 'branding is reserved.'], 422);
        }

        $data = $request->validate([
            'document_type' => ['sometimes', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/', 'unique:notary_settings,document_type,' . $notarySetting->id],
            'category' => ['sometimes', 'string', 'max:120'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'requires_court_stamp' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        if (isset($data['document_type']) && $data['document_type'] === 'branding') {
            return response()->json(['success' => false, 'message' => 'branding is reserved.'], 422);
        }

        $notarySetting->fill($data)->save();

        return response()->json([
            'success' => true,
            'data' => $notarySetting->fresh(),
        ]);
    }
}

