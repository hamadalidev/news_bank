<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Base\BaseController;
use App\Http\Resources\ArticleCollection;
use App\Repositories\ArticleRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Articles
 *
 * APIs for managing news articles
 */
class ArticleController extends BaseController
{
    public function __construct(
        private ArticleRepository $articleRepository
    ) {}

    /**
     * List articles
     *
     * Get a paginated list of news articles with optional search, filtering, and sorting capabilities.
     *
     * @queryParam search string Search term to filter articles by title or description. Example: technology
     * @queryParam category int Filter articles by category ID. Example: 1
     * @queryParam author int Filter articles by author ID. Example: 2
     * @queryParam source int Filter articles by news source ID. Example: 3
     * @queryParam column string Field to sort by. Must be one of: id, title, published_at. Example: published_at
     * @queryParam dir string Sort direction. Must be one of: asc, desc. Example: desc
     * @queryParam length int Number of items per page (default: 10). Example: 20
     *
     * @response 200 scenario="success" {
     *   "success": true,
     *   "message": "Success",
     *   "data": {
     *     "list": [
     *       {
     *         "id": 1,
     *         "title": "Breaking: Technology News",
     *         "description": "Latest developments in technology sector...",
     *         "url": "https://example.com/article/1",
     *         "published_at": "2025-10-19T10:30:00Z",
     *         "source": {
     *           "id": 1,
     *           "name": "Tech Daily"
     *         },
     *         "category": {
     *           "id": 1,
     *           "name": "Technology"
     *         },
     *         "author": {
     *           "id": 1,
     *           "name": "John Smith"
     *         }
     *       },
     *       {
     *         "id": 2,
     *         "title": "Business Market Update",
     *         "description": "Current market trends and analysis...",
     *         "url": "https://example.com/article/2",
     *         "published_at": "2025-10-19T09:15:00Z",
     *         "source": {
     *           "id": 2,
     *           "name": "Business News"
     *         },
     *         "category": {
     *           "id": 2,
     *           "name": "Business"
     *         },
     *         "author": {
     *           "id": 2,
     *           "name": "Jane Doe"
     *         }
     *       }
     *     ],
     *     "pagination": {
     *       "total": 150,
     *       "count": 20,
     *       "per_page": 20,
     *       "current_page": 1,
     *       "total_pages": 8
     *     }
     *   }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $requestData = $request->only(['search', 'category', 'author', 'source', 'column', 'dir', 'length']);
        $articles = $this->articleRepository->index($requestData);

        return $this->successResponse(
            new ArticleCollection($articles)
        );
    }
}
