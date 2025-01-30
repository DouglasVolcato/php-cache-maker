<?php

class SlowProcess
{
    public function getData($param1 = null, $param2 = null, $param3 = null, $param4 = null, $param5 = null)
    {
        sleep(5);
        return (object)[
            'success' => true,
            'message' => 'Success',
            'data1' => 'Some data',
            'data2' => 'Some data',
            'data3' => 'Some data',
            'data4' => 'Some data',
            'data5' => 'Some data',
            'data6' => 'Some data231231232',
        ];
    }
}
