<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SiteContent;
use Illuminate\Http\JsonResponse;

class SiteController extends Controller
{
    public function __construct(private readonly SiteContent $content) {}

    public function bootstrap(): JsonResponse
    {
        return $this->cached($this->content->bootstrap());
    }

    public function home(): JsonResponse
    {
        return $this->cached($this->content->home());
    }

    public function menu(): JsonResponse
    {
        return $this->cached(['categories' => $this->content->menu()]);
    }

    public function gallery(): JsonResponse
    {
        return $this->cached(['items' => $this->content->gallery()]);
    }

    public function events(): JsonResponse
    {
        return $this->cached(['events' => $this->content->events()], 60);
    }

    public function page(string $page): JsonResponse
    {
        return $this->cached(['sections' => $this->content->sections($page)]);
    }

    private function cached(array $data, int $maxAge = 300): JsonResponse
    {
        return response()->json($data)->setPublic()->setMaxAge($maxAge);
    }
}
