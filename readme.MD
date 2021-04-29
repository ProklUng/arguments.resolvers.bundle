# Бандл с кастомными ресолверами аргументов для Symfony

INTERNAL

## Установка

`composer require proklung/arguments-resolvers-bundle`

## Argument resolvers

#### RequestParamsResolver 

Преобразует GET/POST/etc параметры контроллера в аргументы action (если они не помечены в качестве атрибутов при описании роута)

Признак контроллера, подлежащего обработке - интерфейс `RequestQueryInterface`

**Нюанс**: этот ресолвер должен запускаться раньше других. Сейчас его приоритет 50000. 

```php
class QueryController extends AbstractController implements RequestQueryInterface
{
    public function action(Request $request, int $param): Response
    {
        // В $param будет лежать содержимое GET/POST переменной param.
        return new Response('OK');
    }
}
```

#### ArrayRecursiveArgumentResolver

Массив, переданный как параметр в роут, рекурсивно прогоняется на предмет переменных и сервисов.

### Управление

Любой ресолвер из бандла можно запретить посредством опции в конфигурационном файле
 `/config/packages/argument_resolvers.yaml`:
 
```yaml
argument_resolvers:
  # Включить/отключить бандл целиком
  defaults:
    enabled: true
  resolvers:
    # Отключается ресолвер argument_resolvers.argument_resolver_query
    argument_resolvers.argument_resolver_query: false
```
