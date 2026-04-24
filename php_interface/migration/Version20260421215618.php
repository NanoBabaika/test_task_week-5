<?php

namespace Sprint\Migration;


class Version20260421215618 extends Version
{
    protected $author = "admin";

    protected $description = "create_log_iblock";

    protected $moduleVersion = "5.6.2";

    public function up() {
        $helper = $this->getHelperManager();

        //  cоздаем тип инфоблока (если вдруг его еще нет)
        $helper->Iblock()->saveIblockType(array(
            'ID' => 'logs',
            'LANG' => array(
                'ru' => array('NAME' => 'Логи', 'SECTION_NAME' => 'Разделы', 'ELEMENT_NAME' => 'Лог'),
            ),
        ));

        //  Создаем сам инфоблок
        $helper->Iblock()->saveIblock(array(
            'NAME' => 'Логи событий',
            'CODE' => 'LOG', // Это важно, по этому коду будем искать в задании
            'IBLOCK_TYPE_ID' => 'logs',
            'LID' => array('s1'),
            'LIST_PAGE_URL' => '',
            'DETAIL_PAGE_URL' => '',
        ));
    }

    public function down() {
        $helper = $this->getHelperManager();
        $helper->Iblock()->deleteIblockIfExists('LOG');
    }

}
