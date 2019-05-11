<?php

declare(strict_types=1);

namespace AbterPhp\Admin\Grid\Factory\Table;

use AbterPhp\Framework\Grid\Factory\TableFactory;
use AbterPhp\Framework\Grid\Factory\Table\BodyFactory;
use AbterPhp\Admin\Grid\Factory\Table\Header\UserApiKey as HeaderFactory;

class UserApiKey extends TableFactory
{
    /**
     * User constructor.
     *
     * @param HeaderFactory $headerFactory
     * @param BodyFactory   $bodyFactory
     */
    public function __construct(HeaderFactory $headerFactory, BodyFactory $bodyFactory)
    {
        parent::__construct($headerFactory, $bodyFactory);
    }
}