<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Retry\Policy;

use Hyperf\Retry\RetryBudgetInterface;
use Hyperf\Retry\RetryContext;

class BudgetRetryPolicy extends BaseRetryPolicy implements RetryPolicyInterface
{
    private $budget;

    public function __construct(RetryBudgetInterface $retryBudget)
    {
        $this->budget = $retryBudget;
    }

    public function canRetry(RetryContext &$retryContext): bool
    {
        if ($retryContext->isFirstTry()) {
            return true;
        }
        if ($this->budget->consume(true)) {
            return true;
        }
        $retryContext['retryExhausted'] = true;
        return false;
    }

    public function beforeRetry(RetryContext &$retryContext): void
    {
        $this->budget->consume();
    }

    public function start(RetryContext $parentRetryContext): RetryContext
    {
        $this->budget->produce();
        return $parentRetryContext;
    }
}
