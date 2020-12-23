<?php

    /**  
     * Импорт данных из csv-файла 
     * 
     * @param integer $id - код продавца
     * @param boolean $creatup - true - новый, false - редактируем 
     * @param boolean $posm - true - разрешанют POSM-материалы, false - запрещают
     * @param date $datset - дата активности
     */


class Import 
{

    protected $plantTemplate;

    protected $logHandle;

    protected $importFields = ['plant_id',
                                'ID2',
                                'Status',
                                'LatinName',
                                'HebrewName',
                                'HebrewName2',
                                'HebrewName3',
                                'LatinName2',
                                'Description',
                                'Details',
                                'Onati',
                                'Tipus',
                                'Godel',
                                'GodelText',
                                'OfenHitpashtut',
                                'Height',
                                'Weight',
                                'KoterGeza',
                                'GezaNum',
                                'Neshirut',
                                'Tzlalit',
                                'TzuraText',
                                'Tzfifut',
                                'OrShemesh',
                                'OrShemeshHome',
                                'OrShemeshText',
                                'MokedMeshicha',
                                'SignonHitzuv',
                                'MiunTohelet',
                                'MiunMikum',
                                'TzimheyMahal',
                                'GinaKtana',
                                'Lama',
                                'HodsheyPricha',
                                'KisuyPricha',
                                'ColorPricha',
                                'GodelPerah',
                                'TzuraPerah',
                                'MeshechPricha',
                                'PerachText',
                                'HanitatHapri',
                                'KisuyMofaHapri',
                                'ColorHapri',
                                'GodelHapri',
                                'TzuraHapri',
                                'MofaHapri',
                                'IsNoPri',
                                'PriText',
                                'MirkamHale',
                                'HodeshShalechet',
                                'TzevaShalechet',
                                'HodeshLivluv',
                                'YehilutLivluv',
                                'ColorLivluv',
                                'ColorHale',
                                'GodelHale',
                                'TzuraHale',
                                'TzuraHaleText',
                                'MofaGeza',
                                'GezaColor',
                                'GezaKlipa',
                                'GezaTzura',
                                'GezaText',
                                'MinTzemah',
                                'MivneShoresh',
                                'EvarHachil',
                                'KetzevBagrut',
                                'KetzevBagrutText',
                                'ToheletHaim',
                                'HamidutTzemah',
                                'HamidutTzemahText',
                                'RegishutTzemah',
                                'RegishutTzemahText',
                                'MikumText',
                                'HaklimMotza',
                                'HaklimMotzaCountry',
                                'GliliotGananiot',
                                'MigbalotHadam',
                                'MigbalotHadamText',
                                'Kotzim',
                                'LatinFname',
                                'HebrewFname',
                                'Gender',
                                'Zanim',
                                'Sug',
                                'IsMahatTest',
                                'Hashkaya',
                                'HashkayaText',
                                'Shtila',
                                'Karka',
                                'Ribuy',
                                'TipulText',
                                'Gizum',
                                'MervachShtila',
                                'EvarToxic',
                                'SmellTypeMilulHalim',
                                'SmellTypePricha',
                                'SmellPowerMilulHalim',
                                'SmellPowerPricha',
                                'FoodHealth',
                                'MofaNoGananit',
                                'Mashtela',
                                'MashtelaText',
                                'PerachOpen',
                                'MisparTzemach',
                                'HartzahaName',
                                'Tzimuah'];

    protected $dataFile;

    function init() {

        ini_set('memory_limit','500M');
        set_time_limit(0);
        ini_set('max_execution_time',0);

        
        $this->dataFile = 'plants-light.csv';
        if ( isset( $_GET['light'] ) )
        {
            $this->log('== LIGHT MODE ==');
        }

        $this->log('Starting Import...');

        $this->wpConnect();

        $this->deletePlants();

        $this->readCsv();

    }

    function log($string) {
        if ( empty( $this->logHandle ) ) {
            $this->logHandle = fopen('log.txt','w+');
        }

        $string = sprintf('[ %s ] - %s', date('d.m.Y H:i:s'), $string . "\n");

        return fwrite($this->logHandle, $string);
    }

    function wpConnect() {
        require($_SERVER['DOCUMENT_ROOT'] . '/blooma/wp-load.php');
    }

    function deletePlants() {

        $this->log('Deleting plants');

        do {

            $plants = get_posts([
                'post_type' =>  'plant',
                'posts_per_page'    =>  50,
                'post_status'   => 'publish'
            ]);

            foreach ( $plants as $plant ) {
                wp_delete_post( $plant->ID, TRUE );
            }
        } while ( ! empty( $plants ) );


    }

    function readCsv() {

        $this->log('Reading CSV');

        $row = 0;
        $start = microtime(true);

        if (($handle = fopen("data/" . $this->dataFile, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {

                if ( empty( $row ) ) {
                    # First row, use it as a template ["keys map"] for the upcoming records
                    $this->setPlantTemplate($data);
                }
                else {
                    $this->writePlant($data);
                    #$this->log('DEBUG STOP!');
                    #die();
                }

                $row++;
            }
            fclose($handle);
        }

        $this->logDivider();
        $time_elapsed_secs = microtime(true) - $start;
        $this->log("DONE, inserted a total of {$row} plants, it took {$time_elapsed_secs} seconds.");
    }

    function setPlantTemplate($data) {
        if ( ! empty( $this->plantTemplate ) ) {
            return;
        }

        $this->log('Setting Plant Template');

        $this->plantTemplate = array_flip($data);
    }

    function value($field,$data) {
        if ( ! isset( $field, $this->plantTemplate ) ) {
            die('Missing Field ' . $field);
        }

        $numeric = $this->plantTemplate[ $field ];



        if (isset($data[ $numeric ])) {

            $elementValue = $data[ $numeric ];
            $rest = substr($elementValue, 0, 1);
            // Массив ли $data[ $numeric ]
            if ( $rest == ",") { // Да, массиив
                $data[ $numeric ] = explode(",", $elementValue);
            }
            
        }



        return $data[ $numeric ];
    }

    function logDivider() {
        $this->log('===============================');
    }

    function writePlant($data) {

        $name = $this->value('HebrewName', $data);

        $post_id = wp_insert_post([
            'post_type'     =>  'plant',
            'post_title'    =>  $name,
            'post_status'   => 'publish'
        ]);

        foreach ( $this->importFields as $fieldName )  {
            update_field($fieldName, $this->value($fieldName, $data), $post_id);   //от AFX
        }

        $this->log("Finished writing plant ID #{$post_id}");

    }

    function __desturct() {

        $this->log('== Done ==');

        fclose($this->logHandle);
    }

}


$import = new Import;
$import->init();
