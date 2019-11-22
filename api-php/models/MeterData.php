<?php

    use Illuminate\Database\Eloquent\Model as Eloquent;

    class MeterData extends Eloquent {

        use BindsDynamically;

        public function __construct() {
            $this->table = "tb_meter_data";
        }

    }