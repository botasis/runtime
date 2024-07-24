<?php

declare(strict_types=1);

namespace Botasis\Runtime\Router\Exception;

use RuntimeException;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;

/**
 * @internal
 */
abstract class AttributeValueException extends RuntimeException implements FriendlyExceptionInterface
{
}
