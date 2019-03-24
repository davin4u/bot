<?php

namespace App;

class TelegramCommand
{
    /**
     * @var TelegramAPI
     */
    protected $telegram;

    /**
     * @var TelegramCommandRelationProvider
     */
    protected $relations;

    public function __construct(TelegramAPI $telegram, TelegramCommandRelationProvider $relationProvider)
    {
        $this->telegram = $telegram;

        $this->relations = $relationProvider;
    }

    /**
     * Set command dependencies
     * @param array $dependencies
     * @return TelegramCommand $this
     */
    public function setDependencies($dependencies)
    {
        if (! empty($dependencies)) {
            foreach ($dependencies as $key => $dependency) {
                $this->{$key} = $dependency;
            }
        }

        return $this;
    }

    protected function extractRelation()
    {
        if (! empty($this->callback->data['relation'])) {
            return $this->relations->find($this->callback->data['relation']);
        }

        return null;
    }
}
