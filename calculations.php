<?php

class Calculations
{
    protected $frequency;
    protected $transmitting_power;
    protected $matrix;

    public function __construct($frequency = 0, $transmitting_power = 0)
    {
        $this->matrix = array();
        $this->frequency = $frequency;
        $this->transmitting_power = $transmitting_power;
    }

    public function dBm_to_mW($value_dBm)
    {
        return pow(10, ($value_dBm / 10)); // 10^($value_dBm / 10)
    }

    public function mW_to_dBm($value_mW)
    {
        return 10 * log10($value_mW);
    }

    public function free_space_loss($distance, $frequency)
    {
        $frequency = $frequency / 1000000000;
        $fsl_db = 20 * log10($distance) + 20 * log10($frequency) + 92.45;
        return $fsl_db;
    }

    public function calculateDistance($point_1, $point_2)
    {
        return sqrt(pow(($point_1[0] - $point_2[0]), 2) + pow(($point_1[1] - $point_2[1]), 2));
    }

    public function thermal_noise_power($frequency)
    {
        return -174 + 10 * log10($frequency);
    }

    public function SNR($power, $thermal_noise_power_dBm)
    {
        return $power - $thermal_noise_power_dBm;
    }

    public function calculate_snr_for_points($coords_transmitter)
    {
        $points_snr_greater_than_6dB = array();
        $distances = array();
        // echo "matrix: ", $this->matrix;
        foreach ($this->matrix as $point) {
            $distance = $this->calculateDistance($point, $coords_transmitter);
            if ($distance != 0) {
                $fsl_dB = $this->free_space_loss($distance, $this->frequency);
                $PRX = $this->transmitting_power - $fsl_dB;
                $thermal_noise_power_dBm = $this->thermal_noise_power($this->frequency);
                echo "thermal noise: " . $thermal_noise_power_dBm;
                $snr = $this->SNR($PRX, $thermal_noise_power_dBm);
                echo "snr: " . $snr;
                if ($snr > 6) {
                    echo "snr: " . $snr;
                    array_push($distances, $distance);
                    array_push($points_snr_greater_than_6dB, $point);
                }
            }
        }
    }
}
