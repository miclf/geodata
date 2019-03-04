<?php namespace Miclf\Geodata;

/**
 * Helper class to retrieve GeoJSON data for communes.
 */
class GeoJSON
{
    /**
     * Return one or more GeoJSON features using one or more NIS codes.
     *
     * @param  string|array  $nisCodes
     *
     * @return array
     */
    function getByNISCode($nisCodes)
    {
        if (!is_array($nisCodes)) {
            $nisCodes = [$nisCodes];
        }

        $features = [];

        foreach ($nisCodes as $code) {
            $path = __DIR__.'/../belgium/geojson/communes/'.$code.'.geojson';
            $features[] = json_decode(file_get_contents($path));
        }

        // If we got more than one feature, return the array. If only
        // one was requested, we return the feature directly to avoid
        // to return an array with only one entry.
        return (count($nisCodes) > 1) ? $features : $features[0];
    }
}
