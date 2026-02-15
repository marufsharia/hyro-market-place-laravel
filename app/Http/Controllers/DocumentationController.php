<?php

namespace App\Http\Controllers;

use App\Models\Documentation;
use App\Models\DocumentationCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DocumentationController extends Controller
{
    public function index(Request $request)
    {
        $categories = DocumentationCategory::with(['publishedDocumentations'])
            ->orderBy('order')
            ->get();

        $query = Documentation::with('category')
            ->published()
            ->orderBy('order');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%')
                  ->orWhere('excerpt', 'like', '%' . $search . '%');
            });
        }

        if ($request->has('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        if ($request->has('version')) {
            $query->version($request->version);
        }

        $docs = $query->paginate(20);

        $versions = Documentation::published()
            ->distinct()
            ->pluck('version')
            ->sort()
            ->values();

        return Inertia::render('Docs/Index', [
            'categories' => $categories,
            'docs' => $docs,
            'versions' => $versions,
            'filters' => $request->only(['search', 'category', 'version'])
        ]);
    }

    public function show(Documentation $documentation)
    {
        if (!$documentation->is_published) {
            abort(404);
        }

        $documentation->load('category');
        $documentation->incrementViews();

        // Get previous and next docs in same category
        $prevDoc = Documentation::where('category_id', $documentation->category_id)
            ->where('order', '<', $documentation->order)
            ->published()
            ->orderBy('order', 'desc')
            ->first();

        $nextDoc = Documentation::where('category_id', $documentation->category_id)
            ->where('order', '>', $documentation->order)
            ->published()
            ->orderBy('order', 'asc')
            ->first();

        $relatedDocs = Documentation::where('category_id', $documentation->category_id)
            ->where('id', '!=', $documentation->id)
            ->published()
            ->orderBy('order')
            ->limit(5)
            ->get();

        $categories = DocumentationCategory::with(['publishedDocumentations'])
            ->orderBy('order')
            ->get();

        return Inertia::render('Docs/Show', [
            'doc' => $documentation,
            'prevDoc' => $prevDoc,
            'nextDoc' => $nextDoc,
            'relatedDocs' => $relatedDocs,
            'categories' => $categories,
        ]);
    }
}
