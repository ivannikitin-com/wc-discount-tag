# Плагин скидочных меток для WooCommerce
Версия 1.0

## Основная идея
Скидки фактически являются метками (тегами), которые могут произвольно назначаться на любой товар (продукт) WooCommerce.
Каждая скидка включается в том случае, если сработали условия ее активации.
Условия активации скидки называются триггерами.

## Скидки
Скидки определяются в разделе WooCommerce --> Скидки.
Существуют следующие типы скидок:
1. **Фиксированная скидка** -- позволяет отнять или прибавить к цене товара фиксированное значение.
Для уменьшения цены число следует указывать со знаком минус, например, "Скидка 500 р." должна иметь значение -500.
2. **Процентовая скидка** -- позволяет отнять или прибавить к цене товара значение, указанное в процентах.
Для уменьшения цены число следует указывать со знаком минус, например, "Скидка 30%" должна иметь значение -30.
Типы скидок могут быть расширены.

Скидка применяется к товарам, на которые она назначена при срабатывании ЛЮБОГО триггера, который указан в этой скидке. 
Рекомендуется сначала определить триггеры, а потом уже определять скидки.

## Триггеры
Список триггеров должен быть определен заранее в разделе WooCommerce --> Триггеры скидок.
Каждый триггер представляет собой условие, заданное значением, а тип триггера определяет как это условие проверяется.
Сейчас существуют следующие типы триггеров:
1. **IP адрес** -- срабатывает, если IP пользователя указан в списке (поле значение).  
Там можно указать несколько IP, разделяя их запятой или пробелом 
2. **Параметр в URL** -- сессионный триггер, который срабатывает если хотя бы раз пользователь посетил страницу с указанным параметром в URL
Типы триггеров могут быть расширены.

### Блокирующие триггеры
Это специальная разновидность триггеров, которые наоборот, блокируют срабатывание скидки при выполнении условия.  
Например, блокирующий триггер по IP НЕ ПОЗВОЛИТ применить скидки пользователям, зашедшим с этого IP.

### Глобальные триггеры
Эта разновидность триггеров проверяется и срабатывает на глобальном уровне, даже если они явно не указаны в скидках.
Можно представить так, что они автоматически дописываются в любую скидку.

Любой тип триггера может быть как обычным, так блокирующим, глобальным, или глобально блокирующим.
Такая схема позволяет очень гибко управлять правилами срабатывания триггеров.
Например, глобальный блокирующий триггер по IP выключит все существующие скидки для пользователя с этого IP.
Глобальный триггер с параметром `abc=superskidka` применит все скидки для пользователя, который хотя бы раз в сессии откроет страницу
с этим параметром.

## Настройки
Настройки плагина доступны в разделе WooCommerce --> Настройки --> Скидочные метки.
* Режим отладки -- Этот режим включает функции отладки, например, возможность указания любого параметра ip с помощью GET-параметров.
* Коэффициент округления итоговой цены со скидкой -- Указывает число знаков после запятой для округления. Отрицательные числа -- это округление ДО ЗАПЯТОЙ, например, -2 -- округление до сотен рублей.
Важно! Если триггеры скидки не сработали или сработал блокирующий триггер, округления не происходит, цена выводится так, как она указана в свойствах продукта.
