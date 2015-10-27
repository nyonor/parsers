<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 22.10.15
 * Time: 17:11
 */
class SeasonModel
{
	const WINTER = "зима";
	const SUMMER = "лето";
	const ALL_SEASONS = "всесезонка";

	/**
	 * Подпихни сюда строчку - метод создаст класс сезона
	 * @param $stringWithSeasonName string
	 * @return SeasonModel
	 */
	public static function Factory($stringWithSeasonName) {

		$match = null;
		preg_match('/([Зз][Ии][Мм])/is',$stringWithSeasonName,$match);
		if (count($match) > 0 && $match[1] != null)
			return new SeasonModel(SeasonModel::WINTER);

		$match = null;
		preg_match('/([Лл][Ее][Тт])/is',$stringWithSeasonName,$match);
		if (count($match) > 0 && $match[1] != null)
			return new SeasonModel(SeasonModel::SUMMER);

		$match = null;
		preg_match('/([Вв][Сс][Ее][Сс][Ее][Зз][Оо][Нн])/is',$stringWithSeasonName,$match);
		if (count($match) > 0 && $match[1] != null)
			return new SeasonModel(SeasonModel::ALL_SEASONS);
	}

	public function __construct($seasonName) {
		if ($seasonName != self::ALL_SEASONS && $seasonName != self::SUMMER && $seasonName != self::WINTER )
			throw new Exception("Use only constants from SeasonModel class!");

		$this->seasonName = $seasonName;
	}

	protected $seasonName;

	public function GetSeasonName() {
		return $this->seasonName;
	}

}