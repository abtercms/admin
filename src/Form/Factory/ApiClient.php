<?php

declare(strict_types=1);

namespace AbterPhp\Admin\Form\Factory;

use AbterPhp\Admin\Domain\Entities\AdminResource;
use AbterPhp\Admin\Domain\Entities\ApiClient as Entity;
use AbterPhp\Admin\Orm\AdminResourceRepo;
use AbterPhp\Framework\Constant\Html5;
use AbterPhp\Framework\Constant\Session;
use AbterPhp\Framework\Form\Component\Option;
use AbterPhp\Framework\Form\Container\FormGroup;
use AbterPhp\Framework\Form\Element\Input;
use AbterPhp\Framework\Form\Element\MultiSelect;
use AbterPhp\Framework\Form\Element\Select;
use AbterPhp\Framework\Form\Element\Textarea;
use AbterPhp\Framework\Form\Extra\Help;
use AbterPhp\Framework\Form\IForm;
use AbterPhp\Framework\Form\Label\Label;
use AbterPhp\Framework\Html\Component\ButtonWithIcon;
use AbterPhp\Framework\Html\Factory\Button as ButtonFactory;
use AbterPhp\Framework\Html\Helper\Attributes;
use AbterPhp\Framework\Html\Tag;
use AbterPhp\Framework\I18n\ITranslator;
use Opulence\Orm\IEntity;
use Opulence\Orm\OrmException;
use Opulence\Sessions\ISession;

class ApiClient extends Base
{
    protected AdminResourceRepo $adminResourceRepo;

    protected ButtonFactory $buttonFactory;

    /**
     * ApiClient constructor.
     *
     * @param ISession          $session
     * @param ITranslator       $translator
     * @param AdminResourceRepo $adminResourceRepo
     * @param ButtonFactory     $buttonFactory
     */
    public function __construct(
        ISession $session,
        ITranslator $translator,
        AdminResourceRepo $adminResourceRepo,
        ButtonFactory $buttonFactory
    ) {
        parent::__construct($session, $translator);

        $this->adminResourceRepo = $adminResourceRepo;
        $this->buttonFactory     = $buttonFactory;
    }

    /**
     * @param string       $action
     * @param string       $method
     * @param string       $showUrl
     * @param IEntity|null $entity
     *
     * @return IForm
     * @throws OrmException
     */
    public function create(string $action, string $method, string $showUrl, ?IEntity $entity = null): IForm
    {
        assert($entity instanceof Entity, new \InvalidArgumentException());

        $this->createForm($action, $method)
            ->addJsOnly()
            ->addDefaultElements()
            ->addId($entity)
            ->addDescription($entity)
            ->addAdminResources($entity)
            ->addSecret()
            ->addDefaultButtons($showUrl);

        $form = $this->form;

        $this->form = null;

        return $form;
    }

    /**
     * @return $this
     */
    protected function addJsOnly(): ApiClient
    {
        $content    = sprintf(
            '<i class="material-icons">warning</i>&nbsp;%s',
            $this->translator->translate('admin:jsOnly')
        );
        $attributes = Attributes::fromArray([Html5::ATTR_CLASS => 'only-js-form-warning']);

        $this->form[] = new Tag($content, [], $attributes, Html5::TAG_P);

        return $this;
    }

    /**
     * @param Entity $entity
     *
     * @return $this
     */
    protected function addId(Entity $entity): ApiClient
    {
        $formAttributes = Attributes::fromArray([Html5::ATTR_TYPE => Input::TYPE_HIDDEN]);
        $this->form[]   = new Input('id', 'id', $entity->getId(), [], $formAttributes);

        return $this;
    }

    /**
     * @param Entity $entity
     *
     * @return $this
     */
    protected function addDescription(Entity $entity): ApiClient
    {
        $input = new Textarea(
            'description',
            'description',
            $entity->getDescription()
        );
        $label = new Label('description', 'admin:apiClientDescription');

        $this->form[] = new FormGroup($input, $label);

        return $this;
    }

    /**
     * @param Entity $entity
     *
     * @return $this
     * @throws OrmException
     */
    protected function addAdminResources(Entity $entity): ApiClient
    {
        $allUserResources = $this->getUserResources();

        $existingData = [];
        foreach ($entity->getAdminResources() as $adminResource) {
            $existingData[$adminResource->getId()] = $adminResource->getIdentifier();
        }

        $options = $this->createAdminResourceOptions($allUserResources, $existingData);

        $this->form[] = new FormGroup(
            $this->createAdminResourceSelect($options),
            $this->createAdminResourceLabel()
        );

        return $this;
    }

    /**
     * @return AdminResource[]
     * @throws OrmException
     */
    protected function getUserResources(): array
    {
        $userId = (string)$this->session->get(Session::USER_ID);

        return $this->adminResourceRepo->getByUserId($userId);
    }

    /**
     * @param AdminResource[] $allUserResources
     * @param string[]        $existingData
     *
     * @return array
     */
    protected function createAdminResourceOptions(array $allUserResources, array $existingData): array
    {
        $existingIds = array_keys($existingData);

        $options = [];
        foreach ($allUserResources as $userResources) {
            $isSelected = in_array($userResources->getId(), $existingIds, true);
            $options[]  = new Option($userResources->getId(), $userResources->getIdentifier(), $isSelected);
        }

        return $options;
    }

    /**
     * @param Option[] $options
     *
     * @return Select
     */
    protected function createAdminResourceSelect(array $options): Select
    {
        $size       = $this->getMultiSelectSize(
            count($options),
            static::MULTISELECT_MIN_SIZE,
            static::MULTISELECT_MAX_SIZE
        );
        $attributes = Attributes::fromArray([Html5::ATTR_SIZE => [(string)$size]]);

        $select = new MultiSelect('admin_resource_ids', 'admin_resource_ids[]', [], $attributes);

        foreach ($options as $option) {
            $select[] = $option;
        }

        return $select;
    }

    /**
     * @return $this
     */
    protected function addSecret(): ApiClient
    {
        $attributes = Attributes::fromArray([Html5::ATTR_READONLY => null]);
        $input      = new Input('secret', 'secret', '', [], $attributes);
        $label      = new Label('secret', 'admin:apiClientSecret');

        $btnAttributes = Attributes::fromArray(
            [
                Html5::ATTR_ID    => ['generateSecret'],
                'data-positionX'  => ['center'],
                'data-positionY'  => ['top'],
                'data-effect'     => ['fadeInUp'],
                'data-duration'   => ['2000'],
                Html5::ATTR_CLASS => ['pmd-alert-toggle'],
            ]
        );
        $btn           = $this->buttonFactory->createWithIcon(
            'admin:generateSecret',
            'autorenew',
            [],
            [],
            [ButtonWithIcon::INTENT_DANGER, ButtonWithIcon::INTENT_SMALL],
            $btnAttributes,
            HTML5::TAG_A
        );

        $btnContainerAttributes = Attributes::fromArray([Html5::ATTR_CLASS => 'button-container']);
        $helpAttributes         = Attributes::fromArray([Html5::ATTR_ID => ['secretHelp']]);

        $container   = new Tag(null, [], [], Html5::TAG_DIV);
        $container[] = new Tag(
            $btn,
            [],
            $btnContainerAttributes,
            Html5::TAG_DIV
        );
        $container[] = new Help(
            'admin:apiClientSecretHelp',
            [Tag::INTENT_HIDDEN],
            $helpAttributes
        );

        $this->form[] = new FormGroup($input, $label, $container);

        return $this;
    }

    /**
     * @return Label
     */
    protected function createAdminResourceLabel(): Label
    {
        return new Label('admin_resource_ids', 'admin:adminResources');
    }
}
