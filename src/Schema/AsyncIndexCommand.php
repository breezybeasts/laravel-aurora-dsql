<?php

namespace BreezyBeasts\AuroraDsql\Schema;

use Illuminate\Support\Fluent;

class AsyncIndexCommand extends Fluent
{
    /**
     * Mark the index as unique.
     *
     * @return $this
     */
    public function unique(): static
    {
        $this->attributes['unique'] = true;

        return $this;
    }

    /**
     * Set the "if not exists" flag.
     *
     * @return $this
     */
    public function ifNotExist(): static
    {
        $this->attributes['ifNotExists'] = true;

        return $this;
    }

    public function includes($columns = []): static
    {
        $this->attributes['includedColumns'] = $columns;

        return $this;
    }

    /**
     * Specify the nulls first position.
     *
     * @return $this
     */
    public function nullsFirst(): static
    {
        $this->attributes['nullsPosition'] = 'FIRST';

        return $this;
    }

    /**
     * Specify the  nulls last position.
     *
     * @return $this
     */
    public function nullsLast(): static
    {
        $this->attributes['nullsPosition'] = 'LAST';

        return $this;
    }
}
