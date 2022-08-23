<?php


namespace Cruxinator\SingleTableInheritance;



class SingleTableInheritanceObserver
{
    public function saving($model) {
        $model->filterPersistedAttributes();
        $model->setSingleTableType();
    }
}