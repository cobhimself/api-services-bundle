<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks;

use Cob\Bundle\ApiServicesBundle\Models\BaseResponseModel;

/**
 * @codeCoverageIgnore
 */
class ResponseModelWithNonExistentProperty extends BaseResponseModel {

    public function getNonExistentProperty()
    {
        $this->checkForPropertyException('nonExistentProperty');
    }
}
