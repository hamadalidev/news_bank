<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Base\BaseController;
use App\Http\Resources\NewsSourceCollection;
use App\Repositories\NewsSourceRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group News Sources
 *
 * APIs for managing news sources
 */
class NewsSourceController extends BaseController
{
    public function __construct(
        private NewsSourceRepository $newsSourceRepository
    ) {}

    /**
     * List news sources
     *
     * Get a paginated list of news sources with optional search and sorting capabilities.
     *
     * @queryParam search string Search term to filter news sources by name. Example: guardian
     * @queryParam column string Field to sort by. Must be one of: id, name, status. Example: name
     * @queryParam dir string Sort direction. Must be one of: asc, desc. Example: asc
     * @queryParam length int Number of items per page (default: 10). Example: 15
     *
     * @response 200 scenario="success" {
     *   "success": true,
     *   "message": "Success",
     *   "data": {
     *     "list": [
     *       {
     *         "id": 1,
     *         "name": "The Guardian",
     *         "source_enum": "guardian"
     *       },
     *       {
     *         "id": 2,
     *         "name": "NewsAPI",
     *         "source_enum": "newsapi"
     *       },
     *       {
     *         "id": 3,
     *         "name": "NewsData.io",
     *         "source_enum": "newsdata_io"
     *       }
     *     ],
     *     "pagination": {
     *       "total": 8,
     *       "count": 10,
     *       "per_page": 10,
     *       "current_page": 1,
     *       "total_pages": 1
     *     }
     *   }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $requestData = $request->only(['search', 'column', 'dir', 'length']);
        $sources = $this->newsSourceRepository->index($requestData);
        
        return $this->successResponse(
            new NewsSourceCollection($sources)
        );
    }
}
