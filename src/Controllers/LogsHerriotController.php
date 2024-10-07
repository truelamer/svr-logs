<?php

namespace Svr\Logs\Controllers;

use App\Models\data_applications_animals;
use Svr\Logs\Models\LogsHerriot;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use OpenAdminCore\Admin\Facades\Admin;
use OpenAdminCore\Admin\Controllers\AdminController;
use OpenAdminCore\Admin\Form;
use OpenAdminCore\Admin\Grid;
use OpenAdminCore\Admin\Show;
use OpenAdminCore\Admin\Layout\Content;

class LogsHerriotController extends AdminController
{
    protected $model;
    protected $title;
    protected $all_columns_obj;

    public function __construct()
    {
        $this->model = new LogsHerriot();
        $this->title = trans('svr.log_herriot_requests');
        $this->all_columns_obj = Schema::getColumns($this->model->getTable());
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index(Content $content)
    {
        return Admin::content(function (Content $content) {
            $content->header($this->title);
            $content->description(trans('admin.description'));
            $content->body($this->grid());
        });
    }

    /**
     * Create interface.
     *
     * @param Content $content
     */
    public function create(Content $content)
    {
        if (!Schema::hasTable('data.data_applications_animals'))
        {
            admin_toastr('Не существует таблица data.data_applications_animals', 'error');
        } else {
            return Admin::content(function (Content $content) {
                $content->header($this->title);
                $content->description(trans('admin.create'));
                $content->body($this->form());
            });
        }
    }

    /**
     * Edit interface.
     *
     * @param string $id
     * @param Content $content
     *
     * @return Content
     */
    public function edit($id, Content $content)
    {
        if (!Schema::hasTable('data.data_applications_animals'))
        {
            admin_toastr('Не существует таблица data.data_applications_animals', 'error');
        } else {
            return $content
                ->title($this->title)
                ->description(trans('admin.edit'))
                ->row($this->form()->edit($id));
        }

    }

    /**
     * Show interface.
     *
     * @param string $id
     * @param Content $content
     *
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->title($this->title)
            ->description(trans('admin.show'))
            ->body($this->detail($id));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        $grid = new Grid($this->model);
        foreach ($this->all_columns_obj as $key => $value) {
            match ($value) {
                'update_at' => $grid->column($value['name'], __($value['name']))->display(function ($value) {
                    return Carbon::parse($value)->format('Y-m-d / H:m:s');
                })->help(trans('svr.' . $value)),
                // Отображение остальных колонок
                default => $grid->column($value['name'], __('svr.'.$value['name']))->help(trans('svr.' . $value['name'])),
            };
        }

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(LogsHerriot::findOrFail($id));
        foreach ($this->all_columns_obj as $key => $value) {
            match ($value['name']) {
                // Индивидуальные настройки для отображения полей
                'update_at' => $show
                    ->field($value['name'], __('svr.'.$value['name']))
                    ->as(function ($value) {
                        return $value->format('d.m.Y / H:m:s');
                    }),
                'log_herriot_requests_id' => $show->field($value['name'],
                    __('svr.id'))
                    ->as(function ($value) {
                        return $value;
                    }),
                // Отображение остальных полей
                default => $show->field($value['name'], __('svr.'.$value['name'])),
            };
        }
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new LogsHerriot());

        $form->select('application_animal_id', __('svr.application_animal_id'))->options(data_applications_animals::all()->pluck('application_animal_id', 'application_animal_id'))->help(trans('svr.application_animal_id'))->rules('required|integer', ['integer' => trans('validation.integer')]);
        //$form->number('application_animal_id', __('svr.application_animal_id'))->help(trans('svr.application_animal_id'))->rules('integer', ['integer' => trans('validation.integer')]);

        $form->text('application_request_herriot', __('svr.application_request_herriot'))->help(trans('svr.application_request_herriot'))->rules('nullable');
        $form->text('application_response_herriot', __('svr.application_response_herriot'))->help(trans('svr.application_response_herriot'))->rules('nullable');
        $form->text('application_request_application_herriot', __('svr.application_request_application_herriot'))->help(trans('svr.application_request_application_herriot'))->rules('nullable');
        $form->text('application_response_application_herriot', __('svr.application_response_application_herriot'))->help(trans('svr.application_response_application_herriot'))->rules('nullable');
        $form->hidden('update_at', __('update_at'))->help(trans('svr.updated_at'));
        return $form;
    }
}
