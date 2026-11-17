<?php

namespace App\Services;

use App\Contracts\ProductBatchSource;
use App\Services\BatchSources\ProductInventoryBatchSource;
use App\Support\ProductBatch;

/**
 * Where dispatchable batches come from.
 *
 * Every screen asks this service rather than any one table, so when batches
 * start coming from production or from another book as well, they appear in
 * the dropdowns and in the stock checks by adding one class to sources()
 * below — no screen, request or service above this point changes.
 *
 * A batch is addressed by reference: "<source>:<id>", e.g. "inventory:45".
 */
class ProductBatchService
{
    /** @var ProductBatchSource[]|null */
    private $sources;

    /** Add a new source here and it is live everywhere batches are used. */
    public function sources()
    {
        if ($this->sources === null) {
            $this->sources = [
                new ProductInventoryBatchSource(),
            ];
        }

        return $this->sources;
    }

    /** Lets a test or a caller work against a made-up set of sources. */
    public function setSources(array $sources)
    {
        $this->sources = $sources;

        return $this;
    }

    /**
     * Batch options for several products at once, keyed by product id and
     * merged across every source.
     *
     * @return array [productId => [option, ...]]
     */
    public function optionsFor(array $productIds)
    {
        $productIds = array_values(array_unique(array_filter($productIds)));
        $out        = [];

        foreach ($this->sources() as $source) {
            foreach ($source->batchesFor($productIds) as $productId => $batches) {
                foreach ($batches as $batch) {
                    $out[$productId][] = $batch->toOption();
                }
            }
        }

        return $out;
    }

    /** Options for one product. */
    public function optionsForProduct($productId)
    {
        $all = $this->optionsFor([$productId]);

        return isset($all[$productId]) ? $all[$productId] : [];
    }

    /**
     * Row-locks every referenced batch, whichever source it belongs to.
     *
     * @param  array $refs ["inventory:45", ...]
     * @return array [ref => ProductBatch] — a ref that resolves to nothing is
     *               simply absent, which the caller reports as "select a batch"
     */
    public function lock(array $refs)
    {
        return $this->resolve($refs, 'lock');
    }

    /** The same, without locking — for reading. */
    public function find(array $refs)
    {
        return $this->resolve($refs, 'find');
    }

    private function resolve(array $refs, $method)
    {
        $bySource = [];

        foreach (array_filter($refs) as $ref) {
            $parsed = self::parse($ref);
            if ($parsed) {
                $bySource[$parsed['source']][] = $parsed['id'];
            }
        }

        $found = [];

        foreach ($this->sources() as $source) {
            if (empty($bySource[$source->key()])) {
                continue;
            }

            foreach ($source->{$method}(array_unique($bySource[$source->key()])) as $batch) {
                $found[$batch->ref()] = $batch;
            }
        }

        return $found;
    }

    /** "inventory:45" → ['source' => 'inventory', 'id' => '45'] */
    public static function parse($ref)
    {
        if (!is_string($ref) || strpos($ref, ':') === false) {
            return null;
        }

        list($source, $id) = explode(':', $ref, 2);

        return ($source === '' || $id === '') ? null : ['source' => $source, 'id' => $id];
    }
}
