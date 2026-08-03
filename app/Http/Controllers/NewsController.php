<?php

namespace App\Http\Controllers;

use App\Services\NewsService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class NewsController extends Controller
{
    public function __construct(private readonly NewsService $news) {}

    public function index(Request $request)
    {
        $items = $this->news->all();

        if ($items === null) {
            return response()->view('news.unavailable', [], 503);
        }

        $perPage = config('news.per_page');
        $page = LengthAwarePaginator::resolveCurrentPage();

        $paginated = new LengthAwarePaginator(
            collect($items)->forPage($page, $perPage)->values(),
            count($items),
            $perPage,
            $page,
            ['path' => $request->url()]
        );

        return view('news.index', ['news' => $paginated]);
    }

    public function refresh()
    {
        $ok = $this->news->refresh();

        return redirect()
            ->route('news.index')
            ->with($ok ? 'status' : 'error', $ok
                ? 'Berita berhasil di-refresh dari sumber.'
                : 'Gagal refresh berita — backend tidak merespons.');
    }

    public function show(int $id)
    {
        $item = $this->news->find($id);

        if ($item === false) {
            return response()->view('news.unavailable', [], 503);
        }

        abort_if($item === null, 404);

        return view('news.show', ['item' => $item]);
    }
}
