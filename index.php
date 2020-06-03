<?php

namespace ITReviewChannel;

/**
 * Промежуточный скрипт.
 *
 * @package ITReview
 */
abstract class Middleware
{
    /**
     * @var Middleware $next Следующий промежуточный скрипт.
     */
    private Middleware $next;

    /**
     * Связывание.
     *
     * @param  Middleware  $middleware  Промежуточный скрипт.
     */
    public function link(Middleware $middleware)
    {
        $this->next = $middleware;
    }

    /**
     * Обработка.
     *
     * @param  array  $request  Запрос с данными.
     *
     * @return bool
     */
    public function work(array $request): bool
    {
        if (!$this->check($request)) {
            return false;
        }

        if (!empty($this->next)) {
            return $this->next->work($request);
        }

        return true;
    }

    /**
     * Проверка.
     *
     * @param  array  $request  Запрос с данными.
     *
     * @return bool
     */
    abstract protected function check(array $request): bool;
}

/**
 * Проверка возраста.
 *
 * @package ITReview
 */
final class AgeMiddleware extends Middleware
{
    /**
     * {@inheritDoc}
     */
    protected function check(array $request): bool
    {
        if (isset($request['age']) && $request['age'] > 20) {
            echo 'Валидация возраста пройдена.' . PHP_EOL;

            return true;
        }

        echo 'Валидация возраста НЕ пройдена.' . PHP_EOL;

        return false;
    }
}

/**
 * Проверка страны.
 *
 * @package ITReview
 */
final class CountryMiddleware extends Middleware
{
    /**
     * {@inheritDoc}
     */
    protected function check(array $request): bool
    {
        if (isset($request['country']) && $request['country'] == 'Poland') {
            echo 'Валидация страны пройдена.' . PHP_EOL;

            return true;
        }

        echo 'Валидация страны НЕ пройдена.' . PHP_EOL;

        return false;
    }
}

/**
 * Проверка страны.
 *
 * @package ITReview
 */
final class NameMiddleware extends Middleware
{
    /**
     * {@inheritDoc}
     */
    protected function check(array $request): bool
    {
        if (isset($request['name']) && $request['name'] == 'John') {
            echo 'Валидация имени пройдена.' . PHP_EOL;

            return true;
        }

        echo 'Валидация имени НЕ пройдена.' . PHP_EOL;

        return false;
    }
}

/**
 * Обработчик.
 *
 * @package ITReview
 */
abstract class Handler
{
    /**
     * @var Handler $next Следующий обработчик.
     */
    private Handler $next;

    /**
     * Связывание.
     *
     * @param  Handler  $handler  Обработчик.
     */
    public function link(Handler $handler)
    {
        $this->next = $handler;
    }

    /**
     * Обработка.
     *
     * @param  array  $request  Запрос.
     */
    public function work(array $request): void
    {
        if ($this->check($request)) {
            $this->process($request);
        }

        if (!empty($this->next)) {
            $this->next->work($request);
        }
    }

    /**
     * Проверка возможности обработки.
     *
     * @param  array  $request  Запрос.
     *
     * @return bool
     */
    abstract protected function check(array $request): bool;

    /**
     * Обработка запроса.
     *
     * @param  array  $request  Запрос.
     */
    abstract protected function process(array $request): void;
}

/**
 * Обработчик платежей QIWI.
 *
 * @package ITReview
 */
final class QiwiHandler extends Handler
{
    /**
     * {@inheritDoc}
     */
    protected function check(array $request): bool
    {
        return isset($request['payment']) && $request['payment'] == 'QIWI';
    }

    /**
     * {@inheritDoc}
     */
    protected function process(array $request): void
    {
        echo 'Обработка запроса оплаты через QIWI.' . PHP_EOL;

        exit;
    }
}

/**
 * Обработчик платежей Sberbank.
 *
 * @package ITReview
 */
final class SberbankHandler extends Handler
{
    /**
     * {@inheritDoc}
     */
    protected function check(array $request): bool
    {
        return isset($request['payment']) && $request['payment'] == 'Sberbank';
    }

    /**
     * {@inheritDoc}
     */
    protected function process(array $request): void
    {
        echo 'Обработка запроса оплаты через Sberbank.' . PHP_EOL;

        exit;
    }
}

/**
 * Приложение.
 *
 * @package ITReview
 */
final class Application
{
    /**
     * @var array $request Запрос.
     */
    private array $request;
    /**
     * @var Middleware $middleware Промежуточный скрипт.
     */
    private Middleware $middleware;
    /**
     * @var Handler $handler Обработчик.
     */
    private Handler $handler;

    /**
     * Конструктор.
     *
     * @param  array  $request  Запрос.
     */
    public function __construct(array $request = [])
    {
        $this->request = $request;
    }

    /**
     * Добавление промежуточных скриптов.
     *
     * @param  mixed  ...$middleware  Промежуточные скрипты.
     */
    public function addMiddleware(...$middleware): void
    {
        if (empty($middleware)) {
            return;
        }

        foreach ($middleware as $currentMiddleware) {
            if (!($currentMiddleware instanceof Middleware)) {
                echo 'Ошибка создания цепочки Middleware.' . PHP_EOL;
                exit;
            }
        }

        $this->middleware = $middleware[0];

        for ($i = 0; $i < count($middleware); $i++) {
            if ($i == 0) {
                continue;
            }

            $middleware[$i - 1]->link($middleware[$i]);
        }
    }

    /**
     * Добавление обрабочтиков.
     *
     * @param  mixed  ...$handlers  Обработчики.
     */
    public function addHandlers(...$handlers): void
    {
        if (empty($handlers)) {
            return;
        }

        foreach ($handlers as $handler) {
            if (!($handler instanceof Handler)) {
                echo 'Ошибка создания цепочки Handlers.' . PHP_EOL;
                exit;
            }
        }

        $this->handler = $handlers[0];

        for ($i = 0; $i < count($handlers); $i++) {
            if ($i == 0) {
                continue;
            }

            $handlers[$i - 1]->link($handlers[$i]);
        }
    }

    /**
     * Обработка.
     */
    public function handle(): void
    {
        if (!empty($this->middleware) && !$this->middleware->work($this->request)) {
            echo 'Запрос не дошел до обработчика.' . PHP_EOL;
            exit;
        }

        if (!empty($this->handler)) {
            $this->handler->work($this->request);
        }

        echo 'Запрос не обработан, т.к. обработчика не нашлось.' . PHP_EOL;
    }
}

$result = [
    'name' => 'John',
    'country' => 'Poland',
    'age' => '25',
    'payment' => 'QIWI',
];

$application = new Application($result);

$application->addMiddleware(
    new AgeMiddleware(),
    new CountryMiddleware(),
    new NameMiddleware()
);

$application->addHandlers(
    new QiwiHandler(),
    new SberbankHandler()
);

$application->handle();
