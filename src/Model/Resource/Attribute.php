<?php

namespace PanKrok\ShoperAppstoreBundle\Model\Resource;

use PanKrok\ShoperAppstoreBundle\Model\ResourceModel;

final class Attribute extends ResourceModel
{
    const TYPE_TEXT = 0;
    const TYPE_CHECKBOX = 1;
    const TYPE_SELECT = 2;
    protected $url = 'attributes';
}
