<?php

namespace App\Modules\BaseModule\Http\Controllers;

use App\Foundation\Builder\TableBuilder;
use App\Foundation\Builder\TableColumn;
use App\Foundation\Enums\Permissions;
use App\Foundation\Enums\ResponseMessage;
use App\Foundation\Exceptions\ErrorException;
use App\Foundation\Http\Controllers\BaseController;
use App\Foundation\Services\General\Response\WebSuccessResponse;
use App\Modules\BaseModule\Filters\BaseModuleFilters;
use App\Modules\BaseModule\Http\Requests\CreateBaseModuleRequest;
use App\Modules\BaseModule\Http\Requests\UpdateBaseModuleRequest;
use App\Modules\BaseModule\Models\BaseModule;
use App\Modules\BaseModule\Services\BaseModulesDatatableService;
use App\Modules\BaseModule\Services\ChangeBaseModuleStatusService;
use App\Modules\BaseModule\Services\DeleteBaseModuleService;
use App\Modules\BaseModule\Services\StoreBaseModuleService;
use App\Modules\BaseModule\Services\UpdateBaseModuleService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class BaseModuleController extends BaseController
{

    protected string $viewPath = "BaseModule";

    protected string $routePath = "admin.base-modules";

    protected string $moduleName = "BaseModule";

    protected string $createFormType = "popup";

    protected bool $deletionAllowed = true;

    protected bool $displayPageStatistics = false;

    /**
     * Create a new controller instance.
     *
     * @return void
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware('permission:' . Permissions::LIST_BASE_MODULES)->only(['index', 'datatable']);
        $this->middleware('permission:' . Permissions::CREATE_BASE_MODULE)->only(['create', 'store']);
        $this->middleware('permission:' . Permissions::UPDATE_BASE_MODULE)->only(['edit', 'update']);
        $this->middleware('permission:' . Permissions::SHOW_BASE_MODULE)->only(['show']);
        $this->middleware('permission:' . Permissions::CHANGE_BASE_MODULE_STATUS)->only(['changeStatus']);
        $this->middleware('permission:' . Permissions::DELETE_BASE_MODULE)->only(['destroy']);

        $this->middleware('auth:admin');
    }

    /**
     * @return View
     */
    protected function index(): View
    {
        return $this->view($this->viewPath . '::index', [
            'table' => $this->tableColumns,
        ]);
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    protected function datatable(): JsonResponse
    {
        $filters = new BaseModuleFilters(request());

        return (new BaseModulesDatatableService())->setFilters($filters)->execute();
    }

    /**
     * @return View
     */
    protected function create(): View
    {
        return view($this->viewPath . '::create');
    }

    /**
     * @param CreateBaseModuleRequest $createBaseModuleRequest
     * @return JsonResponse
     * @throws Exception
     */
    protected function store(CreateBaseModuleRequest $createBaseModuleRequest): JsonResponse
    {
        try {
            $data = $createBaseModuleRequest->only('name_ar', 'name_en', 'status');

            (new StoreBaseModuleService())->setData($data)->execute();

            return (new WebSuccessResponse(message: ResponseMessage::CREATED_SUCCESSFULLY->getMessage(),
                hasRedirect: true
            ))->toResponse();

        } catch (Exception $exception) {
            throw new ErrorException($exception->getMessage());
        }
    }

    /**
     * @param BaseModule $baseModule
     * @return JsonResponse
     * @throws Exception
     */
    protected function changeStatus(BaseModule $baseModule): JsonResponse
    {
        try {
            (new ChangeBaseModuleStatusService($baseModule))->execute();

            return (new WebSuccessResponse(message: ResponseMessage::UPDATED_SUCCESSFULLY->getMessage()))->toResponse();

        } catch (Exception $exception) {
            throw new Exception(ResponseMessage::SOMETHING_WENT_WRONG->getMessage());
        }
    }

    /**
     * @param BaseModule $baseModule
     * @return View
     */
    protected function edit(BaseModule $baseModule): View
    {
        return view($this->viewPath . '::edit', [
            'row' => $baseModule
        ]);
    }

    /**
     * @param BaseModule $baseModule
     * @param UpdateBaseModuleRequest $updateBaseModuleRequest
     * @return JsonResponse
     * @throws Exception
     */
    protected function update(BaseModule $baseModule, UpdateBaseModuleRequest $updateBaseModuleRequest): JsonResponse
    {
        try {
            $data = $updateBaseModuleRequest->only('name_ar', 'name_en', 'status');

            (new UpdateBaseModuleService($baseModule))->setData($data)->execute();

            return (new WebSuccessResponse(message: ResponseMessage::UPDATED_SUCCESSFULLY->getMessage()))->toResponse();

        } catch (Exception $exception) {
            throw new Exception(ResponseMessage::SOMETHING_WENT_WRONG->getMessage());
        }
    }

    /**
     * @param BaseModule $baseModule
     * @return JsonResponse
     * @throws Exception
     */
    protected function destroy(BaseModule $baseModule): JsonResponse
    {
        try {
            (new DeleteBaseModuleService($baseModule))->execute();

            return (new WebSuccessResponse(message: ResponseMessage::DELETED_SUCCESSFULLY->getMessage()))->toResponse();

        } catch (Exception $exception) {
            throw new Exception(ResponseMessage::SOMETHING_WENT_WRONG->getMessage());
        }
    }

    /**
     * Build table data (header & body)
     * @return void
     */
    protected function table(): void
    {
        $this->tableColumns = (new TableBuilder())
            ->addColumn(TableColumn::create()->setColumn('id')->setName('#')->setIsSortable(true))
            ->addColumn(TableColumn::create()->setColumn('name')->setName('Name')->setIsSortable(true))
            ->addColumn(TableColumn::create()->setColumn('created_at')->setName('Creation date')->setIsSortable(true))
            ->addColumn(TableColumn::create()->setColumn('actions')->setName('Actions'))
            ->generate();
    }

}
