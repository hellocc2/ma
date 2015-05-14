var dataPool = {};
dataPool.length = 0;

//function getProcureStateList() {
//	if (dataPool.procureState == null) {
//		var param = {};
//		param.url='/supplierback/page/supplier_supplierType.do';
//		param.sf=function(result) {
//				dataPool.procureState = result;
//		};
//		$.jpAjax(param);
//	} else {
//		return dataPool.procureState;
//	};
//}

/**
 * 使用时仅限于经常用到却不会怎么改变的数据,最多缓存20个
 * @param url
 * @param value
 */
function putUrlValue(url,value) {
	if (dataPool[url] == null) {
		dataPool[url] = value;
		dataPool.length ++ ;
	}
	
	if (dataPool.length > 20) {
		for (var key in dataPool) {
			if (key != 'length') {
				delete dataPool[key];
				dataPool.length -- ;
				break;
			};
		}
	}
}

/**
 * 获取url相关的数据
 * @param url
 * @returns
 */
function getUrlValue(url) {
	return dataPool[url];
}