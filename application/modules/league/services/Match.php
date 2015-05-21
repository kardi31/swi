<?php

/**
 * League_Service_Match
 *
@author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class League_Service_Match extends MF_Service_ServiceAbstract {
    
    protected $matchTable;
    
    public function init() {
        $this->matchTable = Doctrine_Core::getTable('League_Model_Doctrine_Match');
        parent::init();
    }
    
    public function getMatch($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {    
        return $this->matchTable->findOneBy($field, $id, $hydrationMode);
    }
    
    public function getTimetableDate($league_id,$hydrationMode = Doctrine_Core::HYDRATE_RECORD)
    {
        $q = $this->matchTable->createQuery('m');
        $q->addWhere('m.played = 0')
             ->addWhere('m.league_id = ?',$league_id)
             ->addWhere('m.match_date > NOW()')
             ->groupBy('substr(m.match_date,1,10)');
    
    return $q->execute(array(),$hydrationMode);

    }
    
    public function getPrevMatches($leagueIds){
	$prevMatches = array();
	foreach($leagueIds as $leagueId){
	    $prevMatches[$leagueId] = $this->getPrevClosestDateMatches($leagueId);
	}
	
	return $prevMatches;
    }
    
    public function getNextMatches($leagueIds){
	$nextMatches = array();
	foreach($leagueIds as $leagueId){
	    $nextMatches[$leagueId] = $this->getNextClosestDateMatches($leagueId);
	}
	
	return $nextMatches;
    }
    
    public function getNextClosestDateMatches($leagueId){
	
	$q = Doctrine_Manager::getInstance()->getCurrentConnection();
	$result = $q->execute("select m1.*,t1.name as team1_name,t2.name as team2_name from league_match m1"
		. " LEFT JOIN league_team t1 on m1.team1 = t1.id "
		. " LEFT JOIN league_team t2 on m1.team2 = t2.id "
		. " where DATE(m1.match_date) = (select DATE(m3.match_date) from league_match m3 "
		. " where m3.match_date > NOW() and m3.league_id = $leagueId limit 1)"
		. " and m1.league_id = $leagueId"
		. " ORDER BY HOUR(m1.match_date)");

	return $result->fetchAll(Doctrine_Core::FETCH_ASSOC);
    }
    
    public function getMyNextMatches($group_id){
	
	$q = Doctrine_Manager::getInstance()->getCurrentConnection();
	$result = $q->execute("select m1.*,t1.name as team1_name,t2.name as team2_name from league_match m1"
		. " LEFT JOIN league_team t1 on m1.team1 = t1.id "
		. " LEFT JOIN league_team t2 on m1.team2 = t2.id "
                . " INNER JOIN league_league l on m1.league_id = l.id"
                . " where m1.match_date > NOW()"
		. " and l.group_id like $group_id"
                . " and l.active = 1"
		. " ORDER BY m1.match_date");
	return $result->fetchAll(Doctrine_Core::FETCH_ASSOC);
    }
    
     public function getLastResult($group_id,$hydrationMode = Doctrine_Core::HYDRATE_RECORD)
    {
        $q = $this->matchTable->createQuery('m');
        $q
                ->innerJoin('m.Team1 t1')
                ->innerJoin('m.Team2 t2')
                ->leftJoin('t1.Logo l1')
                ->leftJoin('t2.Logo l2')
                ->leftJoin('m.League l')
                ->addSelect('m.*,l.*,t1.name,t2.name,l1.*,l2.*')
                ->addWhere('l.group_id = ?',$group_id)
                ->addWhere('t1.my_team = 1 or t2.my_team = 1')
                ->addWhere('m.played = 1')
             ->orderBy('m.match_date DESC')
                ->limit(1);
    return $q->fetchOne(array(),$hydrationMode);

    }
    
    public function getPrevClosestDateMatches($leagueId){
	
	$q = Doctrine_Manager::getInstance()->getCurrentConnection();
	$result = $q->execute("select m1.*,t1.name as team1_name,t2.name as team2_name from league_match m1"
		. " LEFT JOIN league_team t1 on m1.team1 = t1.id "
		. " LEFT JOIN league_team t2 on m1.team2 = t2.id "
		. " where DATE(m1.match_date) = (select DATE(m3.match_date) from league_match m3 "
		. " where m3.match_date < NOW() and m3.league_id = $leagueId limit 1)"
		. " and m1.league_id = $leagueId"
		. " ORDER BY HOUR(m1.match_date)");

	return $result->fetchAll(Doctrine_Core::FETCH_ASSOC);
    }
    
    public function getLastResults($hydrationMode = Doctrine_Core::HYDRATE_RECORD)
    {
        $q = $this->matchTable->createQuery('m');
        $q->addWhere('m.played = 1')
             ->orderBy('m.id DESC')
                ->limit(6);
    
    return $q->execute(array(),$hydrationMode);

    }
    
    public function getLeagueNextDate($league_id){
	$q = $this->matchTable->createQuery('m3');
	//select DATE(m3.match_date) from m where m3.match_date > NOW() order by m3.match_date DESC limit 1)
	$q->select('DATE(match_date)');
	$q->where('match_date > NOW()');
	$q->addWhere('league_id = ?',$league_id);
	$q->orderBy('match_date');
	$q->limit(1);
	return $q;
    }
    
    public function getTimetable($league_id,$hydrationMode = Doctrine_Core::HYDRATE_RECORD)
    {
    $q = $this->matchTable->createQuery('m');
    $q->addWhere('m.played = 0')
         ->addWhere('m.league_id = ?',$league_id)
         ->addWhere('m.match_date > NOW()') 
         ->orderBy('m.match_date ASC');
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
         $q = $this->matchTable->createQuery('m');
         $q->where('m.league_id = ?',$league_id)
                 ->addWhere('m.played = 1')
                 ->limit($limit)
                 ->orderBy('m.match_date DESC','m.id DESC')
                 ->groupBy('m.match_date');
         return $q->execute(array(),$hydrationMode);
    }
    
    public function getTeamMatches($team_id,$hydrationMode = Doctrine_Core::HYDRATE_RECORD)
     {
         $q = $this->matchTable->createQuery('m');
         $q->addWhere('m.team1 = ? or m.team2 = ?',array($team_id,$team_id))
                 ->andWhere('m.played = 1');
         return $q->execute(array(),$hydrationMode);
    }
    
       public function getTable($league_id)
      {
           $tabela = array();

           $teamService = new League_Service_Team();
           $druzyny = $teamService->getLeagueTeams($league_id,'id');
           foreach($druzyny as $key=>$druzyna):
               
               $me = $this->getTeamMatches($druzyna['id']);
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
           $tabela = array();

           $teamService = new League_Service_Team();
           $druzyny = $teamService->getLeagueTeams($league_id,'id');
           foreach($druzyny as $key=>$druzyna):
               
               $me = $this->getTeamMatches($druzyna['id']);
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
                'match_date' => MF_Text::timeFormat($values['date'], 'Y-m-d', 'd/m/Y')." ".MF_Text::timeFormat($values['time'.$timeId], 'H:i:s','H:i'),
                'team1' => $values['team'.$i],
                'team2' => $values['team'.($i+1)],
                'played' => 0
            );
            
            $match = $this->matchTable->getRecord();
            $match->fromArray($data);
            $match->save();
        endfor;
        
        
        return "true";
    }
       
    public function getFullOrder($id, $field = 'id', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->matchTable->getFullOrderQuery();
        $q->andWhere('o.' . $field . ' = ?', $id);
        return $q->fetchOne(array(), $hydrationMode);
    }
    
    public function getUserOrders($email, $field = 'email', $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->matchTable->getFullOrderQuery();
        $q->andWhere('u.' . $field . ' like ?', $email);
        $q->addOrderBy('o.created_at');
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getNewOrders($date, $hydrationMode = Doctrine_Core::HYDRATE_RECORD) {
        $q = $this->matchTable->getFullOrderQuery();
        $q->andWhere('o.created_at > ?', $date);
        return $q->execute(array(), $hydrationMode);
    }
    
    public function getAllOrders($countOnly = false) {
        if(true == $countOnly) {
            return $this->matchTable->count();
        } else {
            return $this->matchTable->findAll();
        }
    }
    
    public function getCart() {
        if(!$this->cart) {
            $this->cart = new Order_Model_Cart();
        }
        return $this->cart;
    }
    
    public function saveShootersFromArray($values, League_Model_Doctrine_Match $match) {
        $playerService = new League_Service_Player();
        foreach($values as $key => $value) {
            if(!is_array($value) && strlen($value) == 0) {
                $values[$key] = NULL;
            }
        }
        $licznik = 0;
        $match['Shooters']->delete();
        foreach($values['goal1'] as $key1 => $goal1):
            if(!strlen($goal1))
                continue;
            $licznik++;
            $match['Shooters'][$licznik]['match_id'] = $match['id'];
            $match['Shooters'][$licznik]['player_id'] = $values['player1'][$key1];
            $match['Shooters'][$licznik]['goal'] = $goal1;
        endforeach;
        
        foreach($values['goal2'] as $key2 => $goal2):
            if(!strlen($goal2))
                continue;
            $licznik++;
            $match['Shooters'][$licznik]['match_id'] = $match['id'];
            $match['Shooters'][$licznik]['player_id'] = $values['player2'][$key2];
            $match['Shooters'][$licznik]['goal'] = $goal2;
        endforeach;
        
        foreach($values['new_goal1'] as $new_key1 => $new_goal1):
            if(!strlen($new_goal1))
                continue;
            $licznik++;
            $playerArray = explode(' ',$values['new_player1'][$new_key1]);
            
            $playerValues = array(
              'first_name' =>  $playerArray[1],
                'last_name' => $playerArray[0],
                'team_id' => $match['team1']
            );
            $player1 = $playerService->savePlayerFromArray($playerValues);
            $match['Shooters'][$licznik]['match_id'] = $match['id'];
            $match['Shooters'][$licznik]['player_id'] = $player1['id'];
            $match['Shooters'][$licznik]['goal'] = $new_goal1;
        endforeach;
        
        foreach($values['new_goal2'] as $new_key2 => $new_goal2):
            if(!strlen($new_goal2))
                continue;
            $licznik++;
            $playerArray2 = explode(' ',$values['new_player2'][$new_key2]);
            $playerValues2 = array(
              'first_name' =>  $playerArray2[1],
                'last_name' => $playerArray2[0],
                'team_id' => $match['team2']
            );
            $player2 = $playerService->savePlayerFromArray($playerValues2);
            $match['Shooters'][$licznik]['match_id'] = $match['id'];
            $match['Shooters'][$licznik]['player_id'] = $player2['id'];
            $match['Shooters'][$licznik]['goal'] = $new_goal2;
        endforeach;
        
        $match->save();
        
    }
}
?>