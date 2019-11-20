<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Converters;

use MarcinOrlowski\ResponseBuilder\Contracts\ConverterContract;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Eric Pfeiffer <eric (#) marketingrelevance (.) com>
 * @copyright 2019 Marketing Relevance
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/mrelevance/laravel-api-response-builder
 */

class JsonResourceConverter implements ConverterContract
{
    public function convert($obj, array $config): array
    {
        /** @var \Illuminate\Http\Resources\Json\Resource $obj */
        return $obj->response()->getData(true);
    }
}