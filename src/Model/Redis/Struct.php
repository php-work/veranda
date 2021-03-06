<?php

namespace Veranda\Model\Redis;

use Veranda\Meta;

use Illuminate\Support\Facades\Redis;

/**
 * @todo 本类不支持进程缓存
 * @todo 带键名存储，暂未支持压缩到数组存储与更新版本功能
 */
abstract class Struct extends Meta\Struct
{
    protected static $_prefix = '';
    protected static $_expire = null;
    protected static $_connection   = 'default';

    protected $id;
    public $exists = false;

    public function __construct($id = null)
    {
        if ($id)
        {
            $this->id = $id;
        } else {
            $this->genId();
        }
    }

    public static function find($id)
    {
        $model  = new static($id);

        return $model->load();
    }

    public static function findOrFail($id)
    {
        $model  = new static($id);
        if (!$model->load())  \ver\raise('aborts.redis_model_not_found');

        return $model;
    }

    public static function found($id): self
    {
        $model  = new static($id);
        $model->load();

        return $model;
    }

    public function id()
    {
        return $this->id;
    }

    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    public function load(): ?self
    {
        $key    = static::getKey($this->id);
        $data   = static::redis()->get($key);
        if ($data === null) return null;
        $this->fill(static::decode($data));
        $this->exists = true;

        return $this;
    }

    public function save(): self
    {
        $arr    = $this->toArray();
        $key    = static::getKey($this->id);
        static::redis()->set($key, static::encode($arr));
        static::$_expire && static::redis()->expire($key, static::$_expire);

        !$this->exists && $this->exists = true;
        return $this;
    }

    public function delete(): self
    {
        $key    = static::getKey($this->id);
        static::redis()->delete($key);
        return $this;
    }

    public function genId(): string
    {
        $id = \ver\idgen::sorted62();
        return $this->setId($id)->id();
    }

    protected static function encode($data): string
    {
        return json_encode($data);
    }

    protected static function decode(string $packed)
    {
        return json_decode($packed, true);
    }

    protected static function redis()
    {
        return Redis::connection(static::$_connection);
    }

    protected static function getKey($id): string
    {
        return static::$_prefix."/$id";
    }
}