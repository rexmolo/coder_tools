<?php

abstract class skateschool_data_abstract_instance {

	protected $mdlS;

	/**
	 *
	 * @param $mdl
	 * @param string $appId
	 * @return $this|bool
	 */
	protected function instanceMdl($mdl, $appId = 'skateschool')
	{

		if (is_array($mdl)) {

			foreach ($mdl as $appId => $models) {
				if (is_array($models)) {

					foreach ($models as $model) {
						$this->setMdl($model, $appId);
					}

				} else {
					$this->setMdl($models, $appId);
				}
			}

		} else {
			$this->setMdl($mdl, $appId);
		}

		return $this;
	}


	private function setMdl($mdl, $appId)
	{
		if (!$this->isExistMdl($this->mdlS[$mdl]))
			$this->mdlS[$mdl] = app::get($appId)->model($mdl);
	}

	/**
	 * mdl 是否存在
	 *
	 * @param $mdl
	 *
	 * @return bool
	 */
	private function isExistMdl($mdl)
	{
		if ($this->mdlS[$mdl]) return true;

		return false;
	}
}






