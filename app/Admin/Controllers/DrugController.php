<?php

namespace App\Admin\Controllers;

use App\Models\Drug;
use App\Http\Controllers\Controller;
use App\Models\Photo;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class DrugController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Наркотики')
            ->description('')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Наркотики')
            ->description('')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Наркотики')
            ->description('')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Наркотики')
            ->description('')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Drug);

        $grid->id('ID');
        $grid->street_name('«Уличное» название');
        $grid->city('Город');
        $grid->column('photo_drug','Фотография наркотика')->display(function () {
            $photo = Photo::whereDrugId($this->id)->where('type', 0)->first();

            if ($photo) {
                return $photo->photo;
            } else {
                return '';
            }
        })->image();

        $grid->column('confirm', 'Подтверждение')->display(function ($confirm) {
            if ($confirm) {
                return '✅Подтверждено';
            } else {
                return '❌Не подтверждено';
            }
        });
        $grid->updated_at('Дата изменения');

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
        $show = new Show(Drug::findOrFail($id));

        $show->id('ID');
        $show->street_name('«Уличное» название');
        $show->city('Город');

        $photos = Photo::whereDrugId($id)->where('type', 0)->get();
        foreach ($photos as $key => $photo) {
            $show->field('photo_drug' . $key,'Фотография наркотика ' . ++$key)->as(function () use ($photo) {
                return $photo->photo;
            })->image('',800,800);
        }

        $show->active_substance('Активное вещество');
        $show->symbol('Символ');
        $show->state('Состояние');
        $show->color('Цвет');
        $show->inscription('Надпись');
        $show->shape('Форма');
        $show->weight('Вес таблетки');
        $show->weight_active('Вес действующего вещества');
        $show->description('Описание');
        $show->negative_effect('Негативный эффект');

        $photos = Photo::whereDrugId($id)->where('type', 1)->get();
        foreach ($photos as $key => $photo) {
            $show->field('photo_test' . $key,'Фотография теста ' . ++$key)->as(function () use ($photo) {
                return $photo->photo;
            })->image('',800,800);
        }

        $show->field('confirm', 'Подтверждение')->as(function ($confirm) {
            if ($confirm) {
                return '✅Подтверждено';
            } else {
                return '❌Не подтверждено';
            }
        });
        $show->updated_at('Дата изменения');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Drug);

        $form->text('street_name', '«Уличное» название');
        $form->text('city', 'Город');
        $form->text('active_substance', 'Активное вещество');
        $form->text('symbol', 'Символ');
        $form->text('state', 'Состояние');
        $form->text('color', 'Цвет');
        $form->text('inscription', 'Надпись');
        $form->text('shape', 'Форма');
        $form->text('weight', 'Вес таблетки');
        $form->text('weight_active', 'Вес действующего вещества');
        $form->text('description', 'Описание');
        $form->text('negative_effect', 'Негативный эффект');
        $form->switch('confirm', 'Подтверждение');

        return $form;
    }
}
