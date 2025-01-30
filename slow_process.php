<?php

class SlowProcess
{
    public function getData($param1 = null, $param2 = null, $param3 = null, $param4 = null, $param5 = null)
    {
        sleep(5);
        return (object)[
            'param1' => $param1,
            'param2' => $param2,
            'param3' => $param3,
            'param4' => $param4,
            'param5' => $param5
        ];
    }
}
