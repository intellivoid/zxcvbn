clean:
	rm -rf build

build:
	mkdir build
	ppm --no-intro --compile="src/Zxcvbn" --directory="build"

update:
	ppm --generate-package="src/Zxcvbn"

install:
	ppm --no-intro --no-prompt --fix-conflict --install="build/net.intellivoid.zxcvbn.ppm"

install_fast:
	ppm --no-intro --no-prompt --fix-conflict --skip-dependencies --install="build/net.intellivoid.zxcvbn.ppm"