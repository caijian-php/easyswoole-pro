<?php


namespace App\Event;

/**
 * 类似go的defer，swoole的defer
 * 刚启动一个主事件，便把主事件最后所要触发的一系列事件注入到主事件执行结束之后执行
 * 将一系列事件的绑定集中到一块，方便嵌入事件，代码维护。
 *
 * Class DeferEvent
 * @package App\Event
 */
class DeferEvent
{

}