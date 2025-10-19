<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Base\BaseController;
use App\Http\Resources\CategoryCollection;
use App\Repositories\CategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Categories
 *
 * APIs for managing news categories
 */
class CategoryController extends BaseController
{
    public function __construct(
        private CategoryRepository $categoryRepository
    ) {}

    /**
     * List categories
     *
     * Get a paginated list of news categories with optional search and sorting capabilities.
     *
     * @queryParam search string Search term to filter categories by name. Example: technology
     * @queryParam column string Field to sort by. Must be one of: id, name. Example: name
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
     *         "name": "Technology"
     *       },
     *       {
     *         "id": 2,
     *         "name": "Business"
     *       },
     *       {
     *         "id": 3,
     *         "name": "Sports"
     *       }
     *     ],
     *     "pagination": {
     *       "total": 12,
     *       "count": 10,
     *       "per_page": 10,
     *       "current_page": 1,
     *       "total_pages": 2
     *     }
     *   }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $requestData = $request->only(['search', 'column', 'dir', 'length']);
        $categories = $this->categoryRepository->index($requestData);
        
        return $this->successResponse(
            new CategoryCollection($categories)
        );
    }
}
