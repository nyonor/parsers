<?php
use models\YMModelDetailed;
use models\YMOfferDetailed;
use models\StoredYMFilter;
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 06.04.16
 * Time: 14:52
 */

class YandexMarketApiService implements IYandexMarketApiService
{

	use HttpRequestTrait;

	const YANDEX_MARKET_API_KEY = "OzQ6eUQhnQVOFUXxZJzm8bBheVmnxq";
	const FORMAT = "json";
	const FIND_YM_MODELS_BY_NAME_URL_PATTERN = "https://api.content.market.yandex.ru/v1/model/match.%s";
	const FIND_YM_OFFERS_BY_MODEL_ID = "https://api.content.market.yandex.ru/v1/model/%d/offers.%s";
	const FIND_YM_FILTERS_BY_CAT_ID = "https://api.content.market.yandex.ru/v1/category/%d/filters.%s";
	const YM_CATEGORY_TIRES_ID = 90490;
	const YM_GEO_ID_MOSCOW = 213;
	const DEFAULT_CURRENCY = "rur";
	const FILTERS_FILE_PATH = "files/YMFilters.dat";

	function __construct() {

		$this->headers = [

			"Host: api.content.market.yandex.ru",
			"Accept: */*",
			"Authorization: " . self::YANDEX_MARKET_API_KEY

		];

	}

	/**
	 * Поиск модели яндекс-маркета по имени
	 * @param $model mixed|YMModelDetailed
	 * @return mixed|YMModel|YMModel[]
	 * todo переделать - жуткое говно получилось
	 */
	function FindYMModelsByName($model)
	{
		$url = sprintf(self::FIND_YM_MODELS_BY_NAME_URL_PATTERN, self::FORMAT);

		$requestString = $url . "?";
		$subjectModel = null;

		//на самом деле это порочная практика - или аргумент массив или не массив...
		// лучше сделать отдельные методы
		if (is_array($model) && count($model) > 1) {

			$nameString = "";
			foreach($model as $modelItem) {

				$nameString .= $modelItem->name. " ";

			}
			$requestString .= "name=".urlencode($nameString);
			$requestString .= "&many";
			$subjectModel = $model[0];

		} else {

			$nameString = $model->name;
			$requestString .= "name=".urlencode($nameString);
			$subjectModel = $model;

		}

		$fieldsString = is_array($subjectModel->returnFields) ?
			implode(',',$subjectModel->returnFields) :
			$subjectModel->returnFields == null ? "all" : $subjectModel->returnFields;

		$requestString .= "&currency=".self::DEFAULT_CURRENCY.
			"&fields=".$fieldsString.
			"&geo_id=".$subjectModel->geoId;

		var_dump($requestString);

		$formatRawResult = $this->Request($requestString);

		$decodedRawResult = json_decode($formatRawResult);

		//var_dump($decodedRawResult);die;

		/*
		 * массив исходных аргументов, заполним его данными, которые вернул API маркета
		 * для этого пробежим раскодированные результаты
		 */
		$originModelsArray = is_array($model) && count($model) > 1 ? $model : [$model];
		$resultDecodedArray = is_array($decodedRawResult->model) ? $decodedRawResult->model : [$decodedRawResult->model];
		foreach($resultDecodedArray as $modelItemRes) {

			foreach($originModelsArray as $originModelArg) {
				//var_dump($originModelArg);die;

				/**
				 * @var $originModelArg YMModelDetailed
				 */

				//нашли совпадение
				if (stripos($originModelArg->name, $modelItemRes->name) !== false ){
					//var_dump($modelItemRes);

					// заметь - здесь значение переданное в аргументе заменяется на значение полученное от яндекс апи
					$originModelArg->name = $modelItemRes;
					//$originModelArg->category = $modelItemRes->category;
					$originModelArg->price = $modelItemRes->price;
					$originModelArg->photo = $modelItemRes->photo;
					$originModelArg->vendor = $modelItemRes->vendor;
					$originModelArg->ymModelId = $modelItemRes->id;
					$originModelArg->offerCount = $modelItemRes->offerCount;
					$originModelArg->ymModelJsonRaw = $formatRawResult;
					$originModelArg->ymModelName = $modelItemRes->name;

					break;
				}

			}

		}

		return $originModelsArray;
	}

	/**
	 * ВОзвращает строку json от апи маркета
	 * @param int $ymModelId
	 * @param $ymGeoId
	 * @param $ymReturnFields string
	 * @param array $filtersDictionary
	 * @param $sortBy string
	 * @param int $page
	 * @param int $ymCategoryId
	 * @param int $count
	 * @return mixed|\models\YMOfferDetailed[]|string
	 * @throws Exception
	 */
	function FindYMOffersByModel($ymModelId, $ymGeoId, $ymReturnFields, $filtersDictionary, $sortBy, $page = 1, $ymCategoryId = self::YM_CATEGORY_TIRES_ID, $count = 1)
	{
		// TODO: Implement FindYMOffersByModel() method.

		$url = sprintf(self::FIND_YM_OFFERS_BY_MODEL_ID."?fields="
			.$ymReturnFields."&geo_id="
			.$ymGeoId."&sort=".$sortBy
			."&count=".$count
			."&page=".$page,
			$ymModelId,
			self::FORMAT);

		$filterParamsString = $this->GetPreparedUrlParamsByFiltersDict($filtersDictionary, $ymCategoryId, $ymGeoId);

		var_dump($url.$filterParamsString);

		$res = $this->Request($url.$filterParamsString);

		//$decoded = json_decode($res);

		/*$results = [];
		foreach($decoded->offers->items as $item) {

			$offer = new YMOfferDetailed();
			$offer->jsonRaw = json_encode($item);
			$results[] = $offer;

		}*/


		//return $results;

		return $res;

	}

	/**
	 * Получает данные по фильтрам от yandex-market,
	 * которые отнсятся к категории
	 * @param int $categoryId
	 * @param int $geoId
	 * @return stdClass
	 */
	function FindFiltersByYMCategoryId($categoryId = self::YM_CATEGORY_TIRES_ID, $geoId = self::YM_GEO_ID_MOSCOW) {

		//если файл с фильтрами есть и в нем есть нужные категория и геоИд то достанем объект из файла
		//иначе обращаемся к АПИ-маркета и достаем данные по фильтрам и добавляем их в файл

		//var_dump("TOTAL COUNT OF UNSERILIZED.." . count(unserialize(file_get_contents(self::FILTERS_FILE_PATH))));

		if (file_exists(self::FILTERS_FILE_PATH)) {

			//var_dump("1b");

			$fileContentUnserialized = unserialize(file_get_contents(self::FILTERS_FILE_PATH));

			//var_dump($fileContentUnserialized);//die;

			/**
			 * @var $storedFilterModel StoredYMFilter
			 */
			foreach($fileContentUnserialized as $storedFilterModel) {

				//нашел в файле
				if ($storedFilterModel->ymGeoId == $geoId && $storedFilterModel->ymCategoryId == $categoryId) {

					//var_dump("2b");
					$decoded = json_decode($storedFilterModel->jsonRaw);
					//var_dump($decoded);//die;
					return json_decode($storedFilterModel->jsonRaw);

				}

			}

			//если же не нашел, то делаем запрос к апи, добавляем данные в файл и возвращаем декодированный ответ
			$json = $this->CallApiForFilters($categoryId, $geoId);
			$storedFilterModel = new StoredYMFilter();
			$storedFilterModel->ymCategoryId = $categoryId;
			$storedFilterModel->ymGeoId = $geoId;
			$storedFilterModel->jsonRaw = $json;
			$fileContentUnserialized[] = $storedFilterModel;

			//сохраняем в файл
			file_put_contents(self::FILTERS_FILE_PATH, serialize($fileContentUnserialized));

			//var_dump("3b");

			return json_decode($json);

		}
		//если же файл не существет, то содадим его и добавим туда инфу от АПИ по фильтрам
		else {

			//var_dump("4b");

			$json = $this->CallApiForFilters($categoryId, $geoId); //todo этот запрос не учитывается в счетчике запросов!
			//$f = fopen(self::FILTERS_FILE_PATH,'w');

			$fileContentArray = [];
			$storedFilterModel = new StoredYMFilter();
			$storedFilterModel->jsonRaw = $json;
			$storedFilterModel->ymCategoryId = $categoryId;
			$storedFilterModel->ymGeoId = $geoId;
			$fileContentArray[] = $storedFilterModel;

			file_put_contents(self::FILTERS_FILE_PATH, serialize($fileContentArray));
			//fclose($f);

			return json_decode($json);

		}

	}


	protected function CallApiForFilters($categoryId, $geoId) {

		$url = sprintf(self::FIND_YM_FILTERS_BY_CAT_ID."?geo_id=".$geoId."&filter_set=all&description=1",
			$categoryId, self::FORMAT);
		$rawRes = $this->Request($url);

		return $rawRes;
	}

	protected function GetPreparedUrlParamsByFiltersDict(array $filtersDictionary, $ymCategoryId, $ymGeoId) {

		//var_dump($filtersDictionary);die;

		//$resultString = "";

		$filtersStd = $this->FindFiltersByYMCategoryId($ymCategoryId, $ymGeoId);

		if ($filtersStd->errors != null)
			throw new Exception("Фильтры не найдены! " . implode('; ',$filtersStd->errors));

		$url = "&";
		$filters = [];
		foreach($filtersDictionary as $ymFilterId => $filterValue) {

			//найдем такой фильтр в доступных
			/**
			 * @var $foundFilter stdClass
			 */
			$foundFilter = null;

			foreach ($filtersStd->filters as $ymFilterStd) {

				//echo $ymFilterStd->id . " COMPARE TO " . $ymFilterId."<br/>";

				if ($ymFilterStd->id == $ymFilterId){
					$foundFilter = $ymFilterStd;
					break;
				}
			}

			if ($foundFilter == null)
				throw new Exception("НЕ существующий фильтр id!");

			//если значение фильтра это перечисление
			if($foundFilter->type == 'ENUMERATOR') {

				$valueId = null;
				foreach($foundFilter->options as $valueOptionStd) {

					//var_dump ("Lookig inside `" . $valueOptionStd->valueText . "` for `" . $filterValue . "`");

					if (mb_stripos($valueOptionStd->valueText, $filterValue) !== false ||
						stripos($valueOptionStd->valueText, $filterValue) !== false ||
						$valueOptionStd->valueText == $filterValue){

						$filters[] = $foundFilter->id ."=". $valueOptionStd->valueId;
						break;

					}

				}

			}



			//значение - число
			if($foundFilter->type == 'NUMERIC') {

				if ($filterValue >= $foundFilter->minValue && $filterValue <= $foundFilter->maxValue) {

					$filters[] = $foundFilter->id ."=". $filterValue;

				}

			}

			if($foundFilter->type == 'BOOL') {

				if ($filterValue == 1){
					$filters[] = $foundFilter->id ."=". "y";
				} else if ($filterValue == 0) {
					$filters[] = $foundFilter->id ."=". "n";
				}

			}

			//var_dump($filters);
			//var_dump($foundFilter);
		}

		if (count($filters) != count($filtersDictionary)){

			$requested = array_keys($filtersDictionary);
			$found = $filters;

			var_dump($requested);
			var_dump($found);

			$res = array_diff($found, $requested);

			throw new Exception("Количество найденных фильтров не соответствует запрошенным!
			Не найдены следующие ключи " . implode(',', $res));

		}


		//var_dump($url.implode('&', $filters));

		$resultString = $url.implode('&', $filters);
		//var_dump($resultString);

		//die;

		return $resultString;

	}

	public function TestReq($url){

		echo "EXEC TEST REQ!!! for url " . $url . "<br/><br/>";
		print_r($this->Request($url));

	}

}