<?php

namespace App\Repositories;

use App\Models\Tour;
use App\Models\Trip;
use App\Models\User;
use App\Models\Promo;
use Ramsey\Uuid\Uuid;
use App\Models\Activity;
use App\Models\Calendar;
use App\Jobs\SendVerifyMail;
use App\Models\Accomodation;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\Contracts\BaseRepository;

abstract class AbstractEloquentRepository implements BaseRepository
{
    /**
     * Name of the Model with absolute namespace
     *
     * @var string
     */
    protected $modelName;

    /**
     * Instance that extends Illuminate\Database\Eloquent\Model
     *
     * @var Model
     */
    protected $model;

    /**
     * get logged in user
     *
     * @var User $loggedInUser
     */
    protected $loggedInUser;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setModel();
        $this->loggedInUser = $this->getLoggedInUser();
    }

    public function setModel()
    {
        //check if the class exists
        if (class_exists($this->modelName)) {
            $this->model = new $this->modelName;

            //check object is a instanceof Illuminate\Database\Eloquent\Model
            if (!$this->model instanceof Model) {
                throw new \Exception("{$this->modelName} must be an instance of Illuminate\Database\Eloquent\Model");
            }

        } else {
            throw new \Exception('No model name defined');
        }
    }

    /**
     * Get Model instance
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @inheritdoc
     */
    public function findOne($id)
    {
        return $this->findOneBy(['uid' => $id]);
    }

    /**
     * @inheritdoc
     */
    public function findOneBy(array $criteria)
    {
        return $this->model->where($criteria)->first();
    }

    /**
     * @inheritdoc
     */
    public function findBy(array $searchCriteria = [])
    {
        $limit = !empty($searchCriteria['per_page']) ? (int)$searchCriteria['per_page'] : 15; // it's needed for pagination

        $queryBuilder = $this->model->where(function ($query) use ($searchCriteria) {

            $this->applySearchCriteriaInQueryBuilder($query, $searchCriteria);
        }
        );

        return $queryBuilder->paginate($limit);
    }


    /**
     * Apply condition on query builder based on search criteria
     *
     * @param Object $queryBuilder
     * @param array $searchCriteria
     * @return mixed
     */
    protected function applySearchCriteriaInQueryBuilder($queryBuilder, array $searchCriteria = [])
    {

        foreach ($searchCriteria as $key => $value) {

            //skip pagination related query params
            if (in_array($key, ['page', 'per_page'])) {
                continue;
            }

            //we can pass multiple params for a filter with commas
            $allValues = explode(',', $value);

            if (count($allValues) > 1) {
                $queryBuilder->whereIn($key, $allValues);
            } else {
                $operator = '=';
                $queryBuilder->where($key, $operator, $value);
            }
        }

        return $queryBuilder;
    }

    /**
     * @inheritdoc
     */
    public function save(array $data)
    {
        // generate uid
        $data['uid'] = Uuid::uuid4();

        return $this->model->create($data);
    }

    /**
     * @inheritdoc
     */
    public function update(Model $model, array $data)
    {
        $fillAbleProperties = $this->model->getFillable();

        foreach ($data as $key => $value) {

            // update only fillAble properties
            if (in_array($key, $fillAbleProperties)) {
                $model->$key = $value;
            }
        }

        // update the model
        $model->save();

        // get updated model from database
        $model = $this->findOne($model->uid);

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function findIn($key, array $values)
    {
        return $this->model->whereIn($key, $values)->get();
    }

    /**
     * @inheritdoc
     */
    public function delete(Model $model)
    {
        return $model->delete();
    }

    /**
     * get loggedIn user
     *
     * @return User
     */
    protected function getLoggedInUser()
    {
        $user = \Auth::user();

        if ($user instanceof User) {
            return $user;
        } else {
            return new User();
        }
    }

    public function getExpiredDate($visitDate)
    {     
        $visit = date_create($visitDate);
        $now = date_create(date('Y-m-d'));

        $diff = date_diff($now,$visit)->format("%a");

        if($diff>=0 && $diff<=3){
            return date("Y-m-d H:i:s", strtotime('+12 hours'));
        }elseif($diff>3 && $diff<=4){
            return date("Y-m-d H:i:s", strtotime('+24 hours'));
        }else{
            return date("Y-m-d H:i:s", strtotime('+48 hours'));
        }
    }

    public function checkDate($visitDate)
    {
        $result['day']=(date('N', strtotime($visitDate)) >= 6)?'weekend':'weekday';
        $result['is_peakseason']=false;
        $date= Calendar::where('date',$visitDate)->first();
        if($date){
            if($date->type=='holiday'){
                $result['day']='weekend';
            }else{
                $result['is_peakseason']=true;
            }
        }

        return $result;        
    }

    public function getPriceActivity(array $searchCriteria = [], $activity, $type=null)
    {
        //cek schedule

        $model=new Activity;
        $visit_date = date('Y-m-d');
        if(!empty($searchCriteria['visit_date'])){
            $visit_date = date('Y-m-d', $searchCriteria['visit_date']);
        }

        $adult = 6;
        if(!empty($searchCriteria['adult']) && is_numeric($searchCriteria['adult'])){
            $adult = abs($searchCriteria['adult']);
        }

        $lunch=0;
        if(!empty($searchCriteria['lunch']) && $searchCriteria['lunch']==1 ){
            $lunch = $activity->price_lunch;
        }

        $price_guide=0;
        if(!empty($searchCriteria['guide']) && $searchCriteria['guide']==1 ){
            $price_guide = $activity->price_guide;
        }

        $acco=0;
        $price_acco=0;
        $acco_name=null;
        $parking=0;
        if(!empty($searchCriteria['transport']) && $searchCriteria['transport']==1 ){
            $accomodation = $this->getAccomodation($model, $activity->id, $adult);
            $acco = round($accomodation->price / $adult);
            $price_acco= $accomodation->price;
            $acco_name= $accomodation->name;
            $parking= round($activity->price_parking / $adult );
        }

        $resultDate= $this->checkDate($visit_date);

        $priceActivity= $this->getPriceOfAdmission($model, $activity->id, $adult);
        //return $priceActivity;

        if($resultDate['day']== 'weekday'){
            $price=$priceActivity->price_weekday_adult;
        }else{
            $price=$priceActivity->price_weekend_adult;
        }

        $other = $activity->price_other;
        $guide= round($price_guide/$adult);
        $hpp= ceil (($price+$acco+$other+$lunch+$guide+$parking)/1000)*1000;
        $peakseason=($resultDate['is_peakseason'])? ($hpp*$activity->peak_season/100) :0;
        $profit= ($hpp+$peakseason) * $activity->adult_profit/100;
        $tax=ceil (($hpp+ $peakseason+ $profit)* $activity->tax/100) ;
        $summary_price=$profit+$tax+$hpp+$peakseason;
        $per_pax=ceil($summary_price/1000)*1000;

        $expiredDate=$this->getExpiredDate($visit_date);
        $data= [
            'adult'=>[
                'hpp'=>$hpp,
                'profit'=>$profit,
                'tax'=>$tax,
                'per_pax'=>$per_pax,
            ],
            'activity_name'=>$activity->name,
            'car' =>$acco_name,
            'parking' =>$parking,
            'lunch' =>$lunch,
            'guide' =>$guide,
            'total' => ($per_pax*$adult),
            'visit_date' =>strtotime($visit_date),
            'deadline' =>strtotime($expiredDate),
        ];

        if($type=='order'){
            $data['parking']=$parking;
            $data['tours']=[
                'ticket_price'=>$price
            ];
            $data['tax']=$activity->tax;
            $data['adult_profit']=$activity->adult_profit;
            $data['child_profit']=$activity->child_profit;
            $data['peakseason']=$activity->peak_season;
            $data['is_peakseason']=$resultDate['is_peakseason'];
            $data['other'] =$other;
            $data['accomodation'] =$price_acco;
            $data['child']=[   'hpp'=>0,
                                'profit'=>0,
                                'tax'=>0,
                                'per_pax'=>0,
                            ];
        }

        return $data;
    }

    /**
     * ganti this->model
     */
    public function getPrice(array $searchCriteria = [], $trip, $type=null)
    {
        $model=new Trip;
        $visit_date = date('Y-m-d');
        if(!empty($searchCriteria['visit_date'])){
            $visit_date = date('Y-m-d', $searchCriteria['visit_date']);
        }
        
        $resultDate= $this->checkDate($visit_date);
        $dataTour= $this->getDataTour($trip->id);
        if(!empty($searchCriteria['tours'])){
            $arrTour = explode(',',$searchCriteria['tours']);
            if(count($arrTour)==3){
                $dataTour=$this->getDataTour($trip->id,$arrTour);
            }
        }

        if($resultDate['day']== 'weekday'){
            $tour1_adult=$dataTour[0]->price_weekday_adult;
            $tour1_child=$dataTour[0]->price_weekday_child;

            $tour2_adult=$dataTour[1]->price_weekday_adult;
            $tour2_child=$dataTour[1]->price_weekday_child;

            $tour3_adult=$dataTour[2]->price_weekday_adult;
            $tour3_child=$dataTour[2]->price_weekday_child;
        }else{
            $tour1_adult=$dataTour[0]->price_weekend_adult;
            $tour1_child=$dataTour[0]->price_weekend_child;

            $tour2_adult=$dataTour[1]->price_weekend_adult;
            $tour2_child=$dataTour[1]->price_weekend_child;

            $tour3_adult=$dataTour[2]->price_weekend_adult;
            $tour3_child=$dataTour[2]->price_weekend_child;
        }

        $adult = 6;
        if(!empty($searchCriteria['adult']) && is_numeric($searchCriteria['adult'])){
            $adult = abs($searchCriteria['adult']);
        }
        
        $child = 0;
        if(!empty($searchCriteria['child']) && is_numeric($searchCriteria['child']) ){
            $child = abs( ($searchCriteria['child']>2)?2:$searchCriteria['child']);
        }

        $lunch=0;
        if(!empty($searchCriteria['lunch']) && $searchCriteria['lunch']==1 ){
            $lunch = $trip->price_lunch;
        }

        $price_guide=0;
        if(!empty($searchCriteria['guide']) && $searchCriteria['guide']==1 ){
            $price_guide = $trip->price_guide;
        }

        //jika kosong piye?
        $accomodation = $this->getAccomodation($model, $trip->id, $adult);

        //child
        $hpp_child= ($child==0)?0:ceil(($tour1_child+$tour2_child+$tour3_child+$lunch)/1000)*1000;
        $peakseason_child=($resultDate['is_peakseason'])? ($hpp_child*$trip->peak_season/100) :0;
        $profit_child= ($hpp_child+$peakseason_child) * $trip->child_profit/100;
        $tax_child=ceil ( ($hpp_child+$peakseason_child+$profit_child)* $trip->tax/100);
        $summary_price_child=$profit_child+$tax_child+$hpp_child+$peakseason_child;
        $child_per_pax=ceil($summary_price_child/1000)*1000;

        $acco = round($accomodation->price / $adult);
        $other = $trip->price_other;
        $parking= round( ($trip->price_parking*3)/ $adult );
        $guide= round($price_guide/$adult);
        $hpp= ceil (($tour1_adult+$tour2_adult+$tour3_adult+$acco+$other+$lunch+$guide+$parking)/1000)*1000;
        $peakseason=($resultDate['is_peakseason'])? ($hpp*$trip->peak_season/100) :0;
        $profit= ($hpp+$peakseason) * $trip->adult_profit/100;
        $tax=ceil (($hpp+$peakseason+$profit)* $trip->tax/100) ;
        $summary_price=$profit+$tax+$hpp+$peakseason;
        $adult_per_pax=ceil($summary_price/1000)*1000;

        $expiredDate=$this->getExpiredDate($visit_date);
        $data= [
            'adult'=>[
                'hpp'=>$hpp,
                'profit'=>$profit,
                'tax'=>$tax,
                'per_pax'=>$adult_per_pax,
            ],
            'child'=>[
                'hpp'=>$hpp_child,
                'profit'=>$profit_child,
                'tax'=>$tax_child,
                'per_pax'=>$child_per_pax,
            ],
            'trip_name'=>$trip->name,
            'car' =>$accomodation->name,
            'lunch' =>$lunch,
            'guide' =>$guide,
            'total' => ($adult_per_pax*$adult)+($child_per_pax*$child),
            'visit_date' =>strtotime($visit_date),
            'deadline' =>strtotime($expiredDate),
        ];


        $tours=[
            [
                'id'=>$dataTour[0]->id, 
                'name'=>$dataTour[0]->name
            ],
            [
                'id'=>$dataTour[1]->id, 
                'name'=>$dataTour[1]->name
            ],
            [
                'id'=>$dataTour[2]->id, 
                'name'=>$dataTour[2]->name
            ]
        ];

        if($type=='order'){
            $data['parking']=$parking;
            $data['other'] =$other;
            $data['accomodation'] =$accomodation->price;
            $data['tax']=$trip->tax;
            $data['adult_profit']=$trip->adult_profit;
            $data['child_profit']=$trip->child_profit;
            $data['peakseason']=$trip->peak_season;
            $data['is_peakseason']=$resultDate['is_peakseason'];
             
            $tours[0]['adult_price']=$tour1_adult;
            $tours[0]['child_price']=$tour1_child;

            $tours[1]['adult_price']=$tour2_adult;
            $tours[1]['child_price']=$tour2_child;

            $tours[2]['adult_price']=$tour3_adult;
            $tours[2]['child_price']=$tour3_child;

        }

        $data['tours']=$tours;

        return $data;
    }

    /**
     * bisa diubah ke for atau foreach, jangan map sama filter
     * detect city tour dan city tour bisa beberapa kali
     * selain itu gak bisa duplicate
     */
    public function getDataTour($tripID, $toursID=null)
    {
        if($toursID!=null){
            $tour = collect($toursID)->map(function ($id) use($tripID){
                $data = Tour::whereHas('trips',function($query)use($tripID){
                    $query->where('trip_id',$tripID);
                })->find($id);
                if($data){
                    return $data;
                }
            })->filter()->all();
            if(count($tour)== 3){
                return $tour;
            }
        }

        $tour = Tour::whereHas('trips',function($query)use($tripID){
            $query->where('trip_id',$tripID)->where('tour_trip.is_default',true);
        })->take(3)->get();

        if(count($tour)<1){
            return abort(404);
        }
        return $tour;
    }

    public function getPriceOfAdmission($model, $id, $quantity)
    {        
        $data = $model->with(['prices'=>function($query)use($quantity){
            $query->where(function($query)use($quantity){
                $query->where('max_participant','>=', $quantity) 
                ->where('min_participant','<=', $quantity);
            });
        }])->findOrFail($id);

        if(count($data->prices)<1){
            abort(404);
        }
    
        return $data->prices[0];
    }

    public function getAccomodation($model, $id, $adultQuantity)
    {
        $data = $model->with(['accomodations'=>function($query)use($adultQuantity){
            $query->where(function($query)use($adultQuantity){
                $query->where('max_capacity','>=', $adultQuantity) 
                  ->where('min_capacity','<=', $adultQuantity);
            });
        }])->findOrFail($id);
        
        if(count($data->accomodations)<1){
            abort(404);
        }
        
        return $data->accomodations[0];
    }

    public function createSlug($title, $id = 0)
    {
        // Normalize the title
        $slug = str_slug($title);
        // Get any that could possibly be related.
        // This cuts the queries down by doing it once.
        $allSlugs = $this->getRelatedSlugs($slug, $id);
        // If we haven't used it before then we are all good.
        if (! $allSlugs->contains('slug', $slug)){
            return $slug;
        }
        // Just append numbers like a savage until we find not used.
        for ($i = 1; $i <= 10; $i++) {
            $newSlug = $slug.'-'.$i;
            if (! $allSlugs->contains('slug', $newSlug)) {
                return $newSlug;
            }
        }
        throw new \Exception('Can not create a unique slug');
    }

    public function getPromo($request)
    {
        $promo=[
            'promo_code'=>null,
            'amount'=>0
        ];
        if(!empty($request['promo_code']) ){
            $result = Promo::where('code',$request['promo_code'])->first();
            if($result){
                $promo['promo_code']=$result->code;
                $promo['amount']=$result->amount;
            }
        }

        return $promo;
    }

    protected function getRelatedSlugs($slug, $id = 0)
    {
        return $this->model->select('slug')->where('slug', 'like', $slug.'%')
            ->where('id', '<>', $id)
            ->get();
    }

    public function registerUser($request)
    {
        $findUser = User::where('email',$request['email'])->first();
        if(!$findUser){
            $confirmation_code = str_random(30);
            $user = User::create([
                'username' => $request['name'],
                'email' => $request['email'],
                'uid'=>Uuid::uuid4()->toString(),
                'is_active'=>false,
                'password' => app('hash')->make($request['phone']),
                'confirmation_code' => $confirmation_code
            ]);
    
            $url= 'https://kliktrip.id/register/verify/'.$user->confirmation_code;
    
            $verify = array_merge(config('mail.verify'), ['url' =>$url]);
    
            dispatch(new SendVerifyMail($user->email, $verify));
        }
    }
}