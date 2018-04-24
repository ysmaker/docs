# Гит хуки
- Склонировать репозиторий в корень проекта
- Запустить команду в корне своего проекта
```
rm -rf .git/hooks
ln -s ../docs/hooks .git/hooks
chmod +x .git/hooks/pre-commit
```
