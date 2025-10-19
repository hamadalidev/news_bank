<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Base\BaseController;
use App\Http\Resources\AuthorCollection;
use App\Repositories\AuthorRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Authors
 *
 * APIs for managing authors
 */
class AuthorController extends BaseController
{
    public function __construct(
        private AuthorRepository $authorRepository
    ) {}

    /**
     * List authors
     *
     * Get a paginated list of authors with optional search and sorting capabilities.
     *
     * @queryParam search string Search term to filter authors by name. Example: john
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
     *         "name": "John Doe"
     *       },
     *       {
     *         "id": 2,
     *         "name": "Jane Smith"
     *       }
     *     ],
     *     "pagination": {
     *       "total": 25,
     *       "count": 10,
     *       "per_page": 10,
     *       "current_page": 1,
     *       "total_pages": 3
     *     }
     *   }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $requestData = $request->only(['search', 'column', 'dir', 'length']);
        $authors = $this->authorRepository->index($requestData);

        return $this->successResponse(
            new AuthorCollection($authors)
        );
    }
}
