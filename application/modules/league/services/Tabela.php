<?php

/**
 * League_Service_Tabela
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class League_Service_Tabela extends MF_Service_ServiceAbstract {
    
    protected $tabelaTable;
    
    public function init() {
        $this->tabelaTable = Doctrine_Core::getTable('League_Model_Doctrine_Tabela');
        parent::init();
    }
    
    public function getTabelaForTeam($id, $field = 'team_id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->tabelaTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getTimetableDate($league_id,$hydrationMode = Doctrine_Core::HYDRATE_RECORD)
    {
        $q = $this->tabelaTable->createQuery('m');
        $q->addWhere('m.played = 0')
             ->addWhere('m.league_id = ?',$league_id)
             ->addWhere('m.tabela_date > NOW()')
             ->groupBy('substr(m.tabela_date,1,10)');
    
    return $q->execute(array(),$hydrationMode);

    }
    public function getLastResults($hydrationMode = Doctrine_Core::HYDRATE_RECORD)
    {
        $q = $this->tabelaTable->createQuery('m');
        $q->addWhere('m.played = 1')
             ->orderBy('m.id DESC')
                ->limit(6);
    
    return $q->execute(array(),$hydrationMode);

    }
    public function getTimetable($league_id,$hydrationMode = Doctrine_Core::HYDRATE_RECORD)
    {
    $q = $this->tabelaTable->createQuery('m');
    $q->addWhere('m.played = 0')
         ->addWhere('m.league_id = ?',$league_id)
         ->addWhere('m.tabela_date > NOW()') 
         ->orderBy('m.tabela_date ASC');
    return $q->execute(array(),$hydrationMode);
    }
    
    public function getOrderForm(Order_Model_Doctrine_Order $order = null) {
        $form = new Order_Form_Order();
        if(null != $order) { 
            $form->populate($order->toArray());
        }
        return $form;
    }
    
     public function getResults($league_id,$limit=100,$hydrationMode = Doctrine_Core::HYDRATE_RECORD)
     {
         $q = $this->tabelaTable->createQuery('m');
         $q->where('m.league_id = ?',$league_id)
                 ->addWhere('m.played = 1')
                 ->limit($limit)
                 ->orderBy('m.tabela_date DESC','m.id DESC')
                 ->groupBy('m.tabela_date');
         return $q->execute(array(),$hydrationMode);
    }
    
    public function getTeamTabelaes($team_id,$hydrationMode = Doctrine_Core::HYDRATE_RECORD)
     {
         $q = $this->tabelaTable->createQuery('m');
         $q->addWhere('m.team1 = ? or m.team2 = ?',array($team_id,$team_id))
                 ->andWhere('m.played = 1');
         return $q->execute(array(),$hydrationMode);
    }
    
    
     public function calculateTeamTable($match)
      {
           $tabela = array();

           $matchService = new League_Service_Match();
           $druzyny = array($match['team1'], $match['team2']);
           foreach($druzyny as $key=>$druzyna_id):
               
               $me = $matchService->getTeamMatches($druzyna_id);
            $mecze=0;
            $mecze_zw=0;
            $mecze_re=0;
            $mecze_po=0;
            $bramki_zd=0;
            $bramki_st=0;
            $punkty=0;
            foreach($me as $mecz):
                    $mecze++;
                 if($mecz['team1']==$druzyna_id) //ustawiamy, kt�re bramki s� strzelone a kt�re stracone
                 {
                 $zd=$mecz['goal1'];
                 $st=$mecz['goal2'];
                 }
                 else {
                 $zd=$mecz['goal2'];
                 $st=$mecz['goal1'];
                 }
                  // przypisujemy wartosci do zmiennych
                  $bramki_zd += $zd;
                 $bramki_st += $st;
                 if ($zd>$st) // przypisujemy punkty w zale�no�ci od wyniku
                 {
                 $mecze_zw++;
                 $punkty=$punkty+3;
                 }
                 if ($zd==$st)
                 {
                 $mecze_re++;
                 $punkty=$punkty+1;
                 }
                 if ($zd<$st)
                 {
                 $mecze_po++;
                 }
             endforeach;


         if(!$druzynaObj = $this->getTabelaForTeam($druzyna_id)){
              $druzynaObj = $this->tabelaTable->getRecord();
          }
            $druzynaArray = array();
            $druzynaArray['team_id'] = $druzyna_id;
            $druzynaArray['won'] = $mecze_zw;
            $druzynaArray['points'] = $punkty;
            $druzynaArray['draw'] = $mecze_re;
            $druzynaArray['lost'] = $mecze_po;

            $druzynaArray['goals_scored'] = (int)$bramki_zd;
            $druzynaArray['goals_lost'] = (int)$bramki_st;
            $druzynaArray['games'] = (int)$mecze;
            $druzynaArray['league_id'] = $match['league_id'];

            $druzynaObj->fromArray($druzynaArray);

            $druzynaObj->save();
        
        
       endforeach;

    return $sortedTabela;
      }
    
       public function test($league_id)
      {
           $tabela = array();

           $teamService = new League_Service_Team();
           $druzyny = $teamService->getLeagueTeams($league_id,'id');
           foreach($druzyny as $key=>$druzyna):
               
               $me = $this->getTeamTabelaes($druzyna['id']);
            $mecze=0;
            $mecze_zw=0;
            $mecze_re=0;
            $mecze_po=0;
            $bramki_zd=0;
            $bramki_st=0;
            $punkty=0;
            foreach($me as $mecz):
                    $mecze++;
                 if($mecz['team1']==$druzyna['id']) //ustawiamy, kt�re bramki s� strzelone a kt�re stracone
                 {
                 $zd=$mecz['goal1'];
                 $st=$mecz['goal2'];
                 }
                 else {
                 $zd=$mecz['goal2'];
                 $st=$mecz['goal1'];
                 }
                  // przypisujemy wartosci do zmiennych
                  $bramki_zd += $zd;
                 $bramki_st += $st;
                 if ($zd>$st) // przypisujemy punkty w zale�no�ci od wyniku
                 {
                 $mecze_zw++;
                 $punkty=$punkty+3;
                 }
                 if ($zd==$st)
                 {
                 $mecze_re++;
                 $punkty=$punkty+1;
                 }
                 if ($zd<$st)
                 {
                 $mecze_po++;
                 }
             endforeach;
        $nazwa_dr = $druzyna['name'];

        
        
        
        $tabela[$key] = array($nazwa_dr,$mecze,$mecze_zw,$mecze_re,$mecze_po,$bramki_zd,$bramki_st,$punkty);

       endforeach;

           // sortowanie tabeli
     $numer = 0;
    while(count($tabela)>0){

        $licznik = 0;
        $maxPunkty = $tabela[0][7];
        for($k=0;$k<count($tabela);$k++)
        {
            if($tabela[$k][7]>$maxPunkty)
            {
                $maxPunkty = $tabela[$k][7];
                $licznik = $k;
            }
        }
        $numer++;
        $sortedTabela[$numer] = $tabela[$licznik];
        unset($tabela[$licznik]);
        $tabela = array_values($tabela);

    }
    return $sortedTabela;
      }
    
      
      public function saveMatchToTable($match)
      {
          if(!$team1 = $this->getTabelaForTeam($match['team1'])){
              $team1 = $this->tabelaTable->getRecord();
          }
          
          if(!$team2 = $this->getTabelaForTeam($match['team2'])){
              $team2 = $this->tabelaTable->getRecord();
          }
          $team1Data = array();
          $team2Data = array();
          if($match['goal1']>$match['goal2']){
              $team1Data['won'] = (int)$team1['won'] + 1;
              $team2Data['lost'] = (int)$team2['won'] + 1;
              $team1Data['points'] = (int)$team1['points'] + 3;
          }
          elseif($match['goal1'] == $match['goal2']){
              $team1Data['draw'] = (int)$team1['draw'] + 1;
              $team2Data['draw'] = (int)$team2['draw'] + 1;
              $team1Data['points'] = (int)$team1['points'] + 1;
              $team2Data['points'] = (int)$team2['points'] + 1;
          }
          else{
              $team2Data['won'] = (int)$team2['won'] + 1;
              $team1Data['lost'] = (int)$team1['won'] + 1;
              $team2Data['points'] = (int)$team2['points'] + 3;
          }
          
              $team1Data['goals_scored'] = (int)$team1['goals_scored'] + (int)$match['goal1'];
              $team2Data['goals_scored'] = (int)$team2['goals_scored'] + (int)$match['goal2'];
              $team1Data['goals_lost'] = (int)$team1['goals_lost'] + (int)$match['goal2'];
              $team2Data['goals_lost'] = (int)$team2['goals_lost'] + (int)$match['goal1'];
              $team1Data['games'] = (int)$team1['games'] + 1;
              $team2Data['games'] = (int)$team2['games'] + 1;
              $team1Data['league_id'] = $match['league_id'];
              $team2Data['league_id'] = $match['league_id'];
              
              $team1->fromArray($team1Data);
              $team2->fromArray($team2Data);
          
              $team1->save();
              $team2->save();
      }
      
    public function saveTimetableFromArray($values) {
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        
        for($i=1;$i<=10; $i = $i+2):
            
            $timeId = round($i/2);
            if(!strlen($values['team'.$i])||!strlen($values['team'.($i+1)]))
                    break;
            $data = array(
                'league_id' => $values['league_id'],
                'tabela_date' => MF_Text::timeFormat($values['date'], 'Y-m-d', 'd/m/Y')." ".MF_Text::timeFormat($values['time'.$timeId], 'H:i:s','H:i'),
                'team1' => $values['team'.$i],
                'team2' => $values['team'.($i+1)],
                'played' => 0
            );
            
            $tabela = $this->tabelaTable->getRecord();
            $tabela->fromArray($data);
            $tabela->save();
        endfor;
        
        
        return "true";
    }
       
    public function getFullOrder($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->tabelaTable->getFullOrderQuery();
        $q->andWhere('o.' . $field . ' = ?', $id);
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function getUserOrders($email, $field = 'email', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->tabelaTable->getFullOrderQuery();
        $q->andWhere('u.' . $field . ' like ?', $email);
        $q->addOrderBy('o.created_at');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getNewOrders($date, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->tabelaTable->getFullOrderQuery();
        $q->andWhere('o.created_at > ?', $date);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllOrders($countOnly = false) {
        if(true == $countOnly) {
            return $this->tabelaTable->count();
        } else {
            return $this->tabelaTable->findAll();
        }
    }
    
    public function getCart() {
        if(!$this->cart) {
            $this->cart = new Order_Model_Cart();
        }
        return $this->cart;
    }
    
    public function saveShootersFromArray($values, League_Model_Doctrine_Tabela $tabela) {
        $playerService = new League_Service_Player();
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        $licznik = 0;
        $tabela['Shooters']->delete();
        foreach($values['goal1'] as $key1 => $goal1):
            if(!strlen($goal1))
                continue;
            $licznik++;
            $tabela['Shooters'][$licznik]['tabela_id'] = $tabela['id'];
            $tabela['Shooters'][$licznik]['player_id'] = $values['player1'][$key1];
            $tabela['Shooters'][$licznik]['goal'] = $goal1;
        endforeach;
        
        foreach($values['goal2'] as $key2 => $goal2):
            if(!strlen($goal2))
                continue;
            $licznik++;
            $tabela['Shooters'][$licznik]['tabela_id'] = $tabela['id'];
            $tabela['Shooters'][$licznik]['player_id'] = $values['player2'][$key2];
            $tabela['Shooters'][$licznik]['goal'] = $goal2;
        endforeach;
        
        foreach($values['new_goal1'] as $new_key1 => $new_goal1):
            if(!strlen($new_goal1))
                continue;
            $licznik++;
            $playerArray = explode(' ',$values['new_player1'][$new_key1]);
            
            $playerValues = array(
              'first_name' =>  $playerArray[1],
                'last_name' => $playerArray[0],
                'team_id' => $tabela['team1']
            );
            $player1 = $playerService->savePlayerFromArray($playerValues);
            $tabela['Shooters'][$licznik]['tabela_id'] = $tabela['id'];
            $tabela['Shooters'][$licznik]['player_id'] = $player1['id'];
            $tabela['Shooters'][$licznik]['goal'] = $new_goal1;
        endforeach;
        
        foreach($values['new_goal2'] as $new_key2 => $new_goal2):
            if(!strlen($new_goal2))
                continue;
            $licznik++;
            $playerArray2 = explode(' ',$values['new_player2'][$new_key2]);
            $playerValues2 = array(
              'first_name' =>  $playerArray2[1],
                'last_name' => $playerArray2[0],
                'team_id' => $tabela['team2']
            );
            $player2 = $playerService->savePlayerFromArray($playerValues2);
            $tabela['Shooters'][$licznik]['tabela_id'] = $tabela['id'];
            $tabela['Shooters'][$licznik]['player_id'] = $player2['id'];
            $tabela['Shooters'][$licznik]['goal'] = $new_goal2;
        endforeach;
        
        $tabela->save();
        
    }
}
?>