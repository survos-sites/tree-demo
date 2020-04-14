<?php

namespace App\Controller;

use App\Entity\Location;
use App\Services\AppService;

class CreateLocation
{
    private $appService;

    public function __construct(AppService $appService)
    {
        $this->appService = $appService;
    }

    public function __invoke(Location $data): Location
    {
        // $this->bookPublishingHandler->handle($data);
        // dd($data);

        return $data;
    }
}