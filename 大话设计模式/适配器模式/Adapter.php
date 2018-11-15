<?php

/**
 * 将一个类的接口适配成用户所期待的
 */

//-------------抽象接口---------------

/**
 * 抽象运动员
 * Interface IPlayer
 */
interface IPlayer {
    function Attack();

    function Defense();
}

/**前锋
 * Class Forward
 */
class Forward implements IPlayer {

    function Attack() {
        echo "前锋攻击\n";
    }

    function Defense() {
        echo "前锋防御\n";
    }
}


/**中锋
 * Class Center
 */
class Center implements IPlayer {

    function Attack() {
        echo "中锋攻击\n";
    }

    function Defense() {
        echo "中锋防御\n";
    }
}

//--------------待适配对象-----------

/**姚明                 外籍运动员
 * Class Yaoming
 */
class Yaoming {
    function Yaoming_Attack() {
        echo "姚明进攻\n";
    }

    function Yaoming_Defense() {
        echo "姚明防御\n";
    }
}

//------------适配器--------------

/**
 * 姚明的 适配器
 * Class Adapter
 */
class Yaoming_Adapter implements IPlayer {
    private $_player;

    function __construct() {
        $this->_player = new Yaoming();
    }

    function Attack() {
        $this->_player->Yaoming_Attack();
    }

    function Defense() {
        $this->_player->Yaoming_Defense();
    }
}

$player1 = new Forward();

echo "前锋上场:\n";
$player1->Attack();
$player1->Defense();

echo "\n";

echo "姚明上场:\n";
$yaoming = new Yaoming_Adapter();
$yaoming->Attack();
$yaoming->Defense();