<?php

namespace App\Controller;

use App\Entity\Location;
use App\Services\AppService;

class CreateLocation
{
    public function __construct(private AppService $appService)
    {
    }

    public function __invoke(Location $data): Location
    {
        // $this->bookPublishingHandler->handle($data);
        // dd($data);

        return $data;
    }
}