<?php

namespace Telegram\Bot\Laravel;

use Telegram\Bot\Api;
use Telegram\Bot\BotsManager;
use yii\base\Component;
use yii\di\Container;

/**
 * Class TelegramServiceProvider.
 */
class TelegramComponent extends Component
{


    /**
     * @var array
     */
    public $config = [];

    public function __construct(Container $container)
    {
        $this->registerManager($container);
        $this->registerBindings($container);

    }

    /**
     * Register the manager class.
     *
     * @param Container $container
     *
     * @return void
     */
    protected function registerManager(Container $container): void
    {
        $container->set(BotsManager::class, function () use ($container) {
            return (new BotsManager($this->config))->setContainer($container);
        });
    }

    /**
     * Register the bindings.
     *
     * @param Container $container
     *
     * @return void
     */
    protected function registerBindings(Container $container): void
    {
        $container->set(Api::class, function () use ($container) {
            $manager = $container->get(BotsManager::class);

            return $manager->bot();
        });
    }
}
