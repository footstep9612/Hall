<?php


class MaimtController extends PublicController
{
    public function getNameAction()
    {
        exit(json_encode(['name'=>'maimaiti']));
    }
}