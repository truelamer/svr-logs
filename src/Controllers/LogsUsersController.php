<?php

namespace Svr\Logs\Controllers;

use Svr\Logs\Models\LogsUsers;
use App\Models\System\User;
use App\Models\system_users_tokens;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use OpenAdminCore\Admin\Facades\Admin;
use OpenAdminCore\Admin\Controllers\AdminController;
use OpenAdminCore\Admin\Form;
use OpenAdminCore\Admin\Grid;
use OpenAdminCore\Admin\Show;
use OpenAdminCore\Admin\Layout\Content;
use PhpParser\Token;

class LogsUsersController extends AdminController
{
    protected $model;
    protected $title;
    protected $all_columns_obj;

    public function __construct()
    {
        $this->model = new LogsUsers();
        $this->title = trans('svr.logs_users_actions');
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
        if (!Schema::hasTable('system.system_users') OR !Schema::hasTable('system.system_users_tokens'))
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
        if (!Schema::hasTable('system.system_users') OR !Schema::hasTable('system.system_users_tokens'))
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
                'update_at' => $grid->column($value['name'], __('svr.'.$value['name']))->display(function ($value) {
                    return Carbon::parse($value)->format('Y-m-d / H:m:s');
                })->help(trans('svr.' . $value)),
                'action_created_at' => $grid->column($value['name'], __('svr.'.$value['name']))->display(function ($value) {
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
                'action_created_at' => $show
                    ->field($value['name'], __('svr.'.$value['name']))
                    ->as(function ($value) {
                        return $value->format('d.m.Y / H:m:s');
                    }),
                'log_id' => $show->field($value['name'],
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
        $form = new Form(new LogsUsers());

        $form->select('user_id', __('svr.user_id'))->options(User::all()->pluck('user_last', 'user_id'))->help(trans('svr.user_id'))->rules('required|integer', ['integer' => trans('validation.integer')]);
        $form->select('token_id', __('svr.token_id'))->options(system_users_tokens::all()->pluck('token_value', 'token_id'))->help(trans('svr.token_id'))->rules('required|integer', ['integer' => trans('validation.integer')]);
        //$form->number('user_id', __('svr.user_id'))->help(trans('svr.user_id'))->rules('integer', ['integer' => trans('validation.integer')]);
        //$form->number('token_id', __('svr.token_id'))->help(trans('svr.token_id'))->rules('integer', ['integer' => trans('validation.integer')]);

        $form->text('action_module', __('svr.action_module'))->help(trans('svr.action_module'))->rules('max:32', ['max' => trans('validation.max')]);
        $form->text('action_method', __('svr.action_method'))->help(trans('svr.action_method'))->rules('max:64', ['max' => trans('validation.max')]);
        $form->text('action_data', __('svr.action_data'))->help(trans('svr.action_data'))->rules('nullable');
        $form->hidden('action_created_at', __('svr.action_created_at'))->help(trans('svr.action_created_at'));
        $form->hidden('update_at', __('svr.update_at'))->help(trans('svr.update_at'));
        return $form;
    }
}
