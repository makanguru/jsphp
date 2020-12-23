<?php 



    /**  hmr
     * Заполняем - редактируем баллы
     * 
     * @param integer $id - код продавца
     * @param boolean $creatup - true - новый, false - редактируем 
     * @param boolean $posm - true - разрешанют POSM-материалы, false - запрещают
     * @param date $datset - дата активности
     */
    private function actscores($artmp, $creatup)
    {

        $id = $artmp["id"];
        $posm = $artmp["posagreement"];
        $datset = $artmp["dateset"];

        if ($creatup) { // Создаем новый

            $crossscore = new Crossscore();
            $crossscore->id_seller = $id;
            $crossscore->id_act = 1;
            $crossscore->dateAct = $datset;
            $crossscore->cntpoint = 500;
            $crossscore->typeturnover = "+";
            $crossscore->save(false);

            if (!$posm) {            //hmrhmr(+)
                if ($artmp["status"] > 0) { //Отправка SMS
                    $messg = 'Vitayemo! Vy staly uchasnykom prohramy vid TM "Rollton". Vash potochnyi balans - 500 baliv.';
                    $this->sendsms($artmp["phone"], $messg);
                }
            } //hmrhmr(-)



            if ($posm) {
                $crossscore = new Crossscore();
                $crossscore->id_seller = $id;
                $crossscore->id_act = 2;
                $crossscore->dateAct = $datset;
                $crossscore->cntpoint = 500;
                $crossscore->typeturnover = "+";
                $crossscore->save(false);

                //hmrhmr(+)
                if ($artmp["status"] > 0) { //Отправка SMS
                    $messg = 'Vitayemo! Vy staly uchasnykom prohramy vid TM "Rollton". Vash potochnyi balans - 1000 baliv';
                    $this->sendsms($artmp["phone"], $messg);
                }
                //hmrhmr(-)
            }


            //hmr2(+)
            //$artmp["id_tp"] - торговая точка
            $this->add_scores($artmp["id_tp"]);
            //hmr2(-)


        } else {  // Редактируем имеющийся 

            //Работаем с разрешением POS-материалов
            $agreem = ($posm) ? 500 : 0;

            $score = Crossscore::find()
            ->where(['id_seller' => $id, 'id_act' => 2])
            ->one();

            if (is_null($score)) {
                // Добавляем новую строку в crossscore
                $crossscore = new Crossscore();
                $crossscore->id_seller = $id;
                $crossscore->id_act = 2;
                $crossscore->dateAct = $datset;
                $crossscore->cntpoint = $agreem;
                $crossscore->typeturnover = "+";
                $crossscore->save(false);
            } else {
                $score->dateAct = $datset;
                $score->cntpoint = $agreem;
                $score->update();
            }


        }
        
    }





   /**
     * По значениею ID-точки аяксом получим
     * остальные значения ТТ
     * @param integer $id
     * @return mixed
     */
    public function Gettradepoint($id_tp)
    {

        $hmr_tradepoint = Tradepoint::findOne($id_tp);
        $items = ArrayHelper::toArray($hmr_tradepoint);

        try {
                $items['region'] = Catregion::findOne($items['id_region'])->name;
        } catch (ErrorException $e) {
                $items['region'] = "";
        }


        $strsql = "
            SELECT AVG(SUMIN) AS summa
            FROM (
                SELECT id_tp, MONTH(datetrans) AS  MES , SUM(sumtrans) AS  SUMIN
                FROM  crosstrade A
                WHERE  id_tp = {$id_tp}
                AND  sumtrans > 0
                GROUP BY MONTH(datetrans)
            )B
            GROUP BY  id_tp
        ";

        $items['status'] = $wpdb->get_col( $wpdb->prepare( $strsql) ); 
     


        echo Json::encode($items);
    }

	/**
     * Подготовка к отправке sms
     * $phone - куда отправляем sms
     * $messg - текст sms
     */
    public function sendsms($phone, $messg) 
    {

        $from = "Anno Domeni";
        $to = "+38" . $phone; 
        $text = $messg; //"Vitayemo! Vy staly uchasnykom prohramy vid TM 'Rollton'. Vash potochnyi balans - 500 baliv";
        
        $xml="
        <message>
          <service id='single'   source='Rollton'/>
          <to>$to</to>
          <body content-type='plain/text' encoding='plain'>$text</body>
        </message>
        ";
        $answ = $this->postsmsrequest($xml, 'http://bulk.startmobile.com.ua/clients.php ', 'momentum', 'Gtp9%5F');
    }    


   /**
     * Непосредственная отправка sms
     * $data - xml-сообщение
     * $url - сервис отправки смс-уведомлений
     * $login - логин для подключения к сервису
     * $pwd - пароль для подключения к сервису
     */
    public function postsmsrequest($data, $url, $login, $pwd)
    {
    
            $credent = sprintf('Authorization: Basic %s',base64_encode($login.":".$pwd) );
    
            $params=array('http'=>array('method'=>'POST','content'=>$data, 'header'=>$credent));
    
            $ctx = stream_context_create($params);
    
            @$fp=fopen($url, 'rb', FALSE, $ctx);
    
            if ($fp) {
                  $response = @stream_get_contents($fp);
            } else {
                  $response = false;
            }
        
            return $response;
    }       



/**  
     * Начисление баллов при создании нового участника
     * на основании оборотов продаж с видом начисления = 3 
     * @param $id_tp - код тороговой точки
     */
    private function add_scores($id_tp) 
    {


        $strsql = "
        SELECT aa.tt, aa.obs1, bb.datecalculation, bb.accumulatives, 
                aa.obs1-IF((bb.accumulatives is NULL) = 1, 0, bb.accumulatives) AS difference,
                IF((bb.balances is NULL) = 1, 0, bb.balances) AS balanc
        FROM
          (
                SELECT b.id_tp AS tt, sum(b.sumtrans) AS obs1
                FROM  `crosstradeturnover`  b
                INNER JOIN (
                SELECT a.id_tp AS tt
                         FROM `sellerprofile` a GROUP BY a.id_tp 
                ) d ON d.tt = b.id_tp
                GROUP BY b.id_tp
          ) AS aa
        
        LEFT JOIN
        
          (
              SELECT a1.datecalculation, a1.accumulatives, a1.balances, a1.id_tp
                FROM crossbalances a1
              INNER JOIN  (
              SELECT d.id_tp, MAX(datecalculation) AS Datmax FROM crossbalances d
              GROUP BY d.id_tp
                      ) b1
              ON a1.id_tp = b1.id_tp AND a1.datecalculation = b1.datmax
              GROUP BY a1.id_tp  
            ) AS bb
        ON aa.tt = bb.id_tp
        WHERE aa.tt = {$id_tp}
        ";



       $idtptt = $wpdb->get_col( $wpdb->prepare( $strsql) ); ;

          $newturn = $idtptt['obs1']; //Ооборот для нового кортежа для таблицы crossbalance
        
          $simdiff = $idtptt['difference']; //Разность между свежим и прошлым оборотом
        
        
          if ($simdiff > 0) {  // Рассчитываем баллы - есть что начислять

        
              $sum1 = $simdiff + $idtptt['balanc'];  //Сумма, которую нужно распередеелить
        
              $cntthousent = intval($sum1/200);  // Количество двухсоток в сумме
              $cntballs = $cntthousent * 1000; // Количество баллов
              $ostatok = $sum1 - $cntthousent * 200;  // Нераспределенный остаток
        
              $sellers = Sellerprofile::find()->where(['id_tp' => $idtptt['tt']])->asArray()->all();
        
        
              $counter = 0; // Колечество продавцов
              foreach ($sellers as $value) {  //Расчет количества продавцов
                $counter++;
              }
        
        
              $checkadding = true;  // Чтобы не задваивалась инфа в crossbalance
        
              foreach ($sellers as $sel) { // Зачисление баллов по одной ТТ нескольких продавцам
                  // Проверим, чтобы не задваивалось
                  $cnt = ceil($cntballs/($counter == 0 ? 1 : $counter));
                  $dateAct = date("Y-m-d");
                  $crosss = Crossscore::find()->where(['id_seller' => $sel['id'], 'id_act' => '3',  'cntpoint' => $cnt,  'dateAct' => $dateAct ])->asArray()->one();


        
                  if (!is_null($crosss)) { // Уже начислены баллы
                    $checkadding = false;  
                    break;
                  }
        
        
                  $crossscore = new Crossscore();
                  $crossscore->id_seller = $sel['id'];
                  $crossscore->id_act = 3;
                  $crossscore->dateAct = date("Y-m-d");
                  $crossscore->cntpoint = $cnt;
                  $crossscore->save(false);


        
              }   // Конец цикла зачисления баллов по одной ТТ
        
        
              if ($checkadding) { //Заполним таблицу с остатками
        
                  $balanc = Crossbalances::find()->where(['id_tp' => $idtptt['tt'], 'accumulatives' => $newturn,  'balances' => $ostatok,  'datecalculation' => date("Y-m-d") ])->asArray()->one();
        
        
                  if (!is_null($balanc)) { // Уже введены остатки
                    break;
                  }
        
                  $crossbalances = new Crossbalances();
                  $crossbalances->id_tp = $idtptt['tt'];
                  $crossbalances->datecalculation = date("Y-m-d");
                  $crossbalances->accumulatives = $newturn;
                  $crossbalances->balances = $ostatok;
                  $crossbalances->save(false);
        
              }
        
        
        
          }   // Рассчитываем баллы - есть что начислять - ОКОНЧАНИЕ
   
    

    }   /// Окончание метода расчета баллов по оборотным суммам
    // hmr2(-)














