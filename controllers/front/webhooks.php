<?php
class CocolisWebhooksModuleFrontController extends ModuleFrontController
{
    protected $maintenance = false;

    public function initContent()
    {
    }

    public function postProcess()
    {
    switch (Tools::getValue('event')) {
      case 'ride_published':
        echo('Status changé !');
    }
        exit;
    }
}
