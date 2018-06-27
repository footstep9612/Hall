<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 贸易术语
 * FOB: 是Free on Board 或 Freight on Board 的英文缩写,其中文含义为“装运港船上交货（....指定装运港）”。
 * 使用该术语，卖方应负责办理出口清关手续，在合同规定的装运港和规定的期限内，将货物交到买方指派的船上，
 * 承担货物在装运港越过船舷之前的一切风险，并及时通知买方。
 * 本条中风险转移规则已经《2010年国际贸易术语解释通则》修改，
 * 装运港货物装运上船后，风险转移给买方。
 * （由于2000年解释通则规定之越过船舷风险转移，是否越过船舷不便于举证，故而修改。）
 * C&F: 即“Cost and Freight" 的英文缩写，其中文含义为”成本加运费“使用该术语，
 * 卖方负责按通常的条件租船订舱并支付到目的港的运费，
 * 按合同规定的装运港和装运期限将货物装上船并及时通知买家。
 * CIF: 即”Cost Insurance and Freight" 的英文缩写，其中文含义为“成本加保险费、运费”|。
 * 使用该术语，卖方负责按通常条件租船订舱并支付到目的港的运费，
 * 在合同规定的装运港和装运期限内将货物装上船并负责办理货物运输保险，支付保险费。
 * FCA: 即“Free Carrier" 的英文缩写，其中文含义是“货交承运人”。
 * 使用该术语，卖方负责办理货物出口结关手续，
 * 在合同约定的时间和地点将货物交由买方指定的承运人处置，及时通知买方。
 * CPT: 即 “Carriage Paid to” 的英文缩写，其中文含义为“运费付至指定目的地”，
 * 使用该术语，卖方应自费订立运输契约并支付将货物运至目的地的运费。
 * 在办理货物出口结关手续后，在约定的时间和指定的装运地点将货物交由承运人处理，并及时通知买方。
 * CIP: 即 “Carriage and Insurance Paid to" 的英文缩写，中文含义为“运费、保险费付至指定目的地”。
 * 使用该术语，卖方应自费订立运输契约并支付将货物运至目的地的运费，负责办理保险手续并支付保险费。
 * 在办理货物出口结关手续后，在指定的装运地点将货物交由承运人照管，以履行其交货义务。
 * EXW: 即 “EX Works 的英文缩写，其中文含义为“工厂交货（指定的地点）”。
 * 使用该术语，卖方负责在其所在处所（工厂、工场、仓库等）将货物置于买方处置之下即履行了交货义务。
 * FAS: 即"Free Alongside Ship" 的英文缩写，中文含义为“船边交货（指定装运港）”。
 * 使用该术语，卖方负责在装运港将货物放置码头或驳船上靠近船边，即完成交货。eliv
 * DAT: 即“Delivered At Terminal (insert named terminal port or place of destination) 
 * 其中文含义”运输终端交货“。使用该术语卖方在合同中约定的日期或期限内将货物运到合同规定的港口或目的地的运输终端，
 * 并将货物从抵达的载货运输工具上卸下，交给买方处置时即完成交货。
 * DAP: 即"Delivered At Place"（insertnamed place of destination），
 * 目的地交货（插入指定目的港）。使用该术语，卖方必须签订运输合同，
 * 支付将货物运至指定目的地或指定目的地内的约定的点所发生的运费；
 * 在指定的目的地将符合合同约定的货物放在已抵达的运输工具上交给买方处置时即完成交货。
 * @author zhongyg
 */
class Arithmetic {

    const EXW = '工厂交货';
    const FCA = '货交承运人';
    const FAS = "船边交货";
    const FOB = '船上交货价';
//const FOB = '船上交货价';
    const CFR = '装运港船上交货'; //FOB价+运费+保险
    const CPT = '运费付至';
    const CIF = '成本费加保险费加运费'; //成本加保险费加运费（到岸价格）
    const CIP = ""; //指卖方向其指定的承运人交货，期间卖方必须支付将货物运至目的地的运费，并办理买方货物在运输途中灭失或损坏风险的保险,亦即买方承担卖方交货之后的一切风险和额外费用
    const DAT = '目的地或目的港的集散站交货'; //指卖方在指定的目的地或目的港的集散站卸货后将货物交给买方处置即完成交货，术语所指目的地包括港口。卖方应承担将货物运至指定的目的地或目的港的集散站的一切风险和费用（除进口费用外）。本术语适用于任何运输方式或多式联运。
    const DDP = '完税后交货'; //是指卖方在指定的目的地，办理完进口清关手续，将在交货运输工具上尚未卸下的货物交与买方，完成交货。卖方必须承担将货物运至指定的目的地的一切风险和费用，包括在需要办理海关手续时在目的地应交纳的任何“税费”（包括办理海关手续的责任和风险，以及交纳手续费、关税、税款和其他费用）

    private static $commodity_inspection_fee = 0; //商检费
    private static $insurance_tax_rate = 0; //保险税率
    private static $collection_period = 0; //回款周期
    private static $bank_interest = 0; //银行利息
    private static $funds_occupied = 0; //占用资金比例

    /*
     * 商检费
     */

    public function Setinspection_fee($commodity_inspection_fee) {

        self::$commodity_inspection_fee = $commodity_inspection_fee;
    }

    public function Getinspection_fee() {

        return self::$commodity_inspection_fee;
    }

    /*
     * 保险税率
     */

    public function Setinsurance_tax_rate($insurance_tax_rate) {

        self::$insurance_tax_rate = $insurance_tax_rate;
    }

    public function Getinsurance_tax_rate() {

        return self::$insurance_tax_rate;
    }

    /*
     * 回款周期
     */

    public function Setcollection_period($collection_period) {

        self::$collection_period = $collection_period;
    }

    public function Getcollection_period() {

        return self::$collection_period;
    }

    /*
     * 银行利息
     */

    public function Setbank_interest($bank_interest) {

        self::$bank_interest = $bank_interest;
    }

    public function Getbank_interest() {

        return self::$bank_interest;
    }

    /*
     * 占用资金比例
     */

    public function Setfunds_occupied($funds_occupied) {

        self::$funds_occupied = $funds_occupied;
    }

    public function Getfunds_occupied() {

        return self::$funds_occupied;
    }

    //put your code here
    /* EXW 工厂交货时价格
     * $exw 出厂价     * 
     * $commodity_inspection_fee 商检费
     * $Insurance_tax_rate 保险税率
     * $collection_period 回款周期
     * $bank_interest //银行利息
     * $funds_occupied //占用资金比例
     */

    public function EXW($exw) {
        return ($exw + $this->Getinspection_fee()) / $this->getrate();
    }

    private function getrate() {

        return (1 - $this->Getinsurance_tax_rate() //保险税率
                - $this->Getcollection_period() //回款周期
                * $this->Getbank_interest() //银行利息
                * $this->Getfunds_occupied()//占用资金比例
                / 365);
    }

    /*
     * 货交承运人 价格计算
     * $Land_freight 陆运费
     * $Land_insurance 陆运险
     */

    public function FCA($Land_freight, //陆运费
            $Land_insurance, //陆运险
            $exw) {
        return ($Land_freight //陆运费
                + $Land_insurance //陆运险
                + $this->EXW($exw)) / $this->getrate();
    }

    /*
     * 船边交货 价格计算
     * $Land_freight 陆运费
     * $Land_insurance 陆运险
     */

    public function FAS($Land_freight, //陆运费
            $Land_insurance, //陆运险
            $exw) {
        return ($Land_freight //陆运费
                + $Land_insurance //陆运险
                + $this->EXW($exw)) / $this->getrate();
    }

    /*
     * 装运港船上交货 价格计算
     * $Land_freight 陆运费
     * $Land_insurance 陆运险
     * $port_surcharge 港杂费
     */

    public function FOB($Land_freight, //陆运费
            $Land_insurance, //陆运险
            $port_surcharge, //港杂费
            $exw) {
        return ($Land_freight //陆运费
                + $Land_insurance //陆运险
                + $port_surcharge //港杂费
                + $this->EXW($exw)) / $this->getrate();
    }

    /*
     * 运费付至指定目的地 价格计算
     * $Land_freight 陆运费
     * $Land_insurance 陆运险
     * $port_surcharge 港杂费
     * $inland_transportation_charge 国际运输费
     */

    public function CPT($Land_freight, //陆运费
            $Land_insurance, //陆运险
            $port_surcharge, //港杂费
            $inland_transportation_charge, //国际运输费
            $exw) {
        return ($Land_freight //陆运费
                + $Land_insurance //陆运险
                + $port_surcharge //港杂费
                + $inland_transportation_charge //国际运输费
                + $this->EXW($exw)) / $this->getrate();
    }

    /*
     * 运费付至指定目的地 价格计算
     * $Land_freight 陆运费
     * $Land_insurance 陆运险
     * $port_surcharge 港杂费
     * $inland_transportation_charge 国际运输费
     */

    public function CFR($Land_freight, //陆运费
            $Land_insurance, //陆运险
            $port_surcharge, //港杂费
            $inland_transportation_charge, //国际运输费
            $exw) {
        return ($Land_freight //陆运费
                + $Land_insurance //陆运险
                + $port_surcharge //港杂费
                + $inland_transportation_charge //国际运输费
                + $this->EXW($exw)) / $this->getrate();
    }

    /*
     * 成本加保险费、运费 价格计算
     * $Land_freight 陆运费
     * $Land_insurance 陆运险
     * $port_surcharge 港杂费
     * $inland_transportation_charge 国际运输费
     * $cargo_transportation_insurance货物运输保险
     * "1)如果（EXW合计+陆运费+陆运险+港杂费+ 商检费+国际运费）*1.1*货物运输险率/（1-1.1*货物运输险率-
      保险税率-回款周期*银行利息*占用资金比例/365）﹤8,"
      则报价合计=（EXW合计+陆运费+陆运险+港杂费+ 商检费+国际运费）+8〕/（1 -保险税率-回款周期*银行利息*占用资金比例/365）
      2）如果（EXW合计+陆运费+陆运险+港杂费+ 商检费+国际运费）*1.1*货物运输险率/（1-1.1*“国际运输险率-保险税率-回款周期*银行利息*占用资金比例/365）≥8,或为0
      则报价合计=（EXW合计+陆运费+陆运险+港杂费+ 商检费+国际运费）/（1-1.1*货物运输险率-保险税率-回款周期*银行利息*占用资金比例/365）
     */

    public function CIF($Land_freight, //陆运费
            $Land_insurance, //陆运险
            $port_surcharge, //港杂费
            $inland_transportation_charge, //国际运输费
            $cargo_transportation_insurance, //货物运输保险 
            $exw) {
        $val = ($Land_freight //陆运费
                + $Land_insurance //陆运险
                + $port_surcharge //港杂费
                + $inland_transportation_charge //国际运输费
                + $this->EXW($exw)) * 1.1 * $cargo_transportation_insurance / ($this->getrate() - 1.1 * $cargo_transportation_insurance);
        if ($val >= 8) {
            return ($Land_freight //陆运费
                    + $Land_insurance //陆运险
                    + $port_surcharge //港杂费
                    + $inland_transportation_charge //国际运输费
                    + 8 + $this->EXW($exw)) / ($this->getrate() - 1.1 * $cargo_transportation_insurance);
        } else {
            return ($Land_freight //陆运费
                    + $Land_insurance //陆运险
                    + $port_surcharge //港杂费
                    + $inland_transportation_charge //国际运输费
                    + 8 + $this->EXW($exw)) / ($this->getrate() - 1.1 * $cargo_transportation_insurance);
        }
    }

    /*
     * 运费、保险费付至指定目的地 价格计算
     * $Land_freight 陆运费
     * $Land_insurance 陆运险
     * $port_surcharge 港杂费
     * $inland_transportation_charge 国际运输费
     * $cargo_transportation_insurance货物运输保险
     */

    public function CIP($Land_freight, //陆运费
            $Land_insurance, //陆运险
            $port_surcharge, //港杂费
            $inland_transportation_charge, //国际运输费
            $cargo_transportation_insurance, //货物运输保险 
            $exw) {


        $val = ($Land_freight //陆运费
                + $Land_insurance //陆运险
                + $port_surcharge //港杂费
                + $inland_transportation_charge //国际运输费
                + $this->EXW($exw)) * 1.1 * $cargo_transportation_insurance /
                ($this->getrate() - 1.1 * $cargo_transportation_insurance);
        if ($val >= 8) {
            return ($Land_freight //陆运费
                    + $Land_insurance //陆运险
                    + $port_surcharge //港杂费
                    + $inland_transportation_charge //国际运输费
                    + $this->EXW($exw)) / ($this->getrate() - 1.1 * $cargo_transportation_insurance);
        } else {
            return ($Land_freight //陆运费
                    + $Land_insurance //陆运险
                    + $port_surcharge //港杂费
                    + $inland_transportation_charge //国际运输费
                    + 8 + $this->EXW($exw)) / ($this->getrate() - 1.1 * $cargo_transportation_insurance);
        }
    }

    /*
     * 目的地交货 价格计算DAP
     * $Land_freight 陆运费
     * $Land_insurance 陆运险
     * $port_surcharge 港杂费
     * $inland_transportation_charge 国际运输费
     * $cargo_transportation_insurance 货物运输保险
     * $destination_delivery_fee 目的地送货费

     */

    public function DAP($Land_freight, //陆运费
            $Land_insurance, //陆运险
            $port_surcharge, //港杂费
            $inland_transportation_charge, //国际运输费
            $cargo_transportation_insurance, //货物运输保险 
            $destination_delivery_fee, //目的地送货费
            $exw) {

        $val = ($Land_freight //陆运费
                + $Land_insurance //陆运险
                + $port_surcharge //港杂费
                + $inland_transportation_charge //国际运输费
                + $destination_delivery_fee //目的地送货费
                + $this->EXW($exw)) * 1.1 * $cargo_transportation_insurance /
                ($this->getrate() - 1.1 * $cargo_transportation_insurance);
        if ($val >= 8) {
            return ($Land_freight //陆运费
                    + $Land_insurance //陆运险
                    + $port_surcharge //港杂费
                    + $inland_transportation_charge //国际运输费
                    + $destination_delivery_fee //目的地送货费
                    + $this->EXW($exw)) / ($this->getrate() - 1.1 * $cargo_transportation_insurance);
        } else {
            return ($Land_freight //陆运费
                    + $Land_insurance //陆运险
                    + $port_surcharge //港杂费
                    + $inland_transportation_charge //国际运输费
                    + $destination_delivery_fee //目的地送货费
                    + 8 + $this->EXW($exw)) / ($this->getrate() - 1.1 * $cargo_transportation_insurance);
        }
    }

    /*
     * 运输终端交货 价格计算DAT
     * $Land_freight 陆运费
     * $Land_insurance 陆运险
     * $port_surcharge 港杂费
     * $inland_transportation_charge 国际运输费
     * $cargo_transportation_insurance货物运输保险
     * $destination_delivery_fee目的地送货费

     */

    public function DAT($Land_freight, //陆运费
            $Land_insurance, //陆运险
            $port_surcharge, //港杂费
            $inland_transportation_charge, //国际运输费
            $cargo_transportation_insurance, //货物运输保险
            $destination_delivery_fee, //目的地送货费
            $exw) {
        $val = ($Land_freight //陆运费
                + $Land_insurance //陆运险
                + $port_surcharge //港杂费
                + $inland_transportation_charge //国际运输费
                + $destination_delivery_fee //目的地送货费
                + $this->EXW($exw)) * 1.1 * $cargo_transportation_insurance /
                ($this->getrate() - 1.1 * $cargo_transportation_insurance);
        if ($val >= 8) {
            return ($Land_freight //陆运费
                    + $Land_insurance //陆运险
                    + $port_surcharge //港杂费
                    + $inland_transportation_charge //国际运输费
                    + $destination_delivery_fee //目的地送货费
                    + $this->EXW($exw)) / ($this->getrate() - 1.1 * $cargo_transportation_insurance);
        } else {
            return ($Land_freight //陆运费
                    + $Land_insurance //陆运险
                    + $port_surcharge //港杂费
                    + $inland_transportation_charge //国际运输费
                    + $destination_delivery_fee //目的地送货费
                    + 8 + $this->EXW($exw)) / ($this->getrate() - 1.1 * $cargo_transportation_insurance);
        }
    }

    /*
     * 完税后交货 价格计算DDP
     * $Land_freight 陆运费
     * $Land_insurance 陆运险
     * $port_surcharge 港杂费
     * $inland_transportation_charge 国际运输费
     * $cargo_transportation_insurance货物运输保险
     * $destination_delivery_fee目的地送货费
     * $clearance_fee 目的地清关费
     * $tariff 目的地关税
     * $value_added_tax 目的地增值税
     *     $exw //商检费
     */

    public function DDP($Land_freight, //陆运费
            $Land_insurance, //陆运险
            $port_surcharge, //港杂费
            $inland_transportation_charge, //国际运输费
            $cargo_transportation_insurance, //货物运输保险
            $destination_delivery_fee, //目的地送货费
            $clearance_fee, //目的地清关费
            $tariff, //目的地关税
            $value_added_tax, //目的地增值税
            $exw) {
        $val = (($Land_freight //陆运费
                + $Land_insurance //陆运险
                + $port_surcharge //港杂费
                + $inland_transportation_charge //国际运输费
                //目的地送货费
                + $this->EXW($exw)) * (1 + $tariff) * (1 + $value_added_tax) + $clearance_fee + $destination_delivery_fee) * 1.1 * $cargo_transportation_insurance /
                ($this->getrate() - 1.1 * $cargo_transportation_insurance);
        if ($val >= 8) {
            return (($Land_freight //陆运费
                    + $Land_insurance //陆运险
                    + $port_surcharge //港杂费
                    + $inland_transportation_charge //国际运输费
                    //目的地送货费
                    + $this->EXW($exw)) * (1 + $tariff) * (1 + $value_added_tax) + $clearance_fee + $destination_delivery_fee) * 1.1 * $cargo_transportation_insurance /
                    ($this->getrate() - 1.1 * $cargo_transportation_insurance);
        } else {
            return (($Land_freight //陆运费
                    + $Land_insurance //陆运险
                    + $port_surcharge //港杂费
                    + $inland_transportation_charge //国际运输费
                    //目的地送货费
                    + $this->EXW($exw)) * (1 + $tariff) * (1 + $value_added_tax) + $clearance_fee + $destination_delivery_fee + 8) * 1.1 * $cargo_transportation_insurance /
                    ($this->getrate() - 1.1 * $cargo_transportation_insurance);
        }
    }

}
