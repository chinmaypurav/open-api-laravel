<?php

namespace Chinmay\OpenApiLaravel;

use Chinmay\OpenApi\MediaType;
use Chinmay\OpenApi\RequestBody;
use Chinmay\OpenApi\Schema;
use Illuminate\Support\Collection;

class RuleResolver
{
    public function resolveBody(\ReflectionParameter $requestClass): RequestBody
    {
        $class = $requestClass->getType()->getName();

        $mediaType = new MediaType;

        $rules = (new $class)->rules();

        $schema = new Schema('object');

        $rules = Collection::make($rules);

        $properties = Collection::make();

        foreach ($rules as $attribute => $ruleSet) {
            $property = [];
            if (in_array('required', $ruleSet)) {
                $schema->putRequired($attribute);
            }

            $property['type'] = config('openapi.defaults.property_type', 'string');

            if (in_array('string', $ruleSet)) {
                $property['type'] = 'string';
            }
            if (in_array('password', $ruleSet)) {
                $property['type'] = 'string';
                $property['format'] = 'password';
            }
            if (in_array('numeric', $ruleSet)) {
                $property['type'] = 'number';
                $property['format'] = 'float';
            }
            if (in_array('integer', $ruleSet)) {
                $property['type'] = 'integer';
                $property['format'] = 'int64';
            }
            $properties->put($attribute, $property);

        }

        $schema->setProperties($properties->toArray());

        $mediaType->setSchema($schema);

        return new RequestBody($mediaType);
    }
}
