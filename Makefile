#!make

include .env
export $(shell sed 's/=.*//' .env)

update:
	@bash update.sh $${version}

develop:
	bash develop.sh $${version}

build: update
	cp develop-install.sh $${version}
	docker build -t javanile/vtiger:$${version} ./$${version}

push: build
	git add .
	git commit -am "Update images" && true
	git push
	docker login
	docker push javanile/vtiger:$${version}

lint:
	@docker run --rm -i hadolint/hadolint < $${version}/Dockerfile
