# Minimal makefile for Sphinx documentation
#

# You can set these variables from the command line.
SPHINXOPTS    =
SPHINXBUILD   = sphinx-build
SOURCEDIR     = .
BUILDDIR      = _build
STATICDIR     = ../_static
IMAGESDIR     = $(STATICDIR)/images

built = $(wildcard $(BUILDDIR))

# Put it first so that "make" without argument is like "make help".
help:
	@$(SPHINXBUILD) -M help "$(SOURCEDIR)" "$(BUILDDIR)" $(SPHINXOPTS) $(O)

.PHONY: help Makefile

.PHONE: clean
clean:
	rm -fR $(BUILDDIR)
	rm -fR api
	rm -fR api.rst

# If we have a $(BUILDDIR), clean it
$(built): clean

init:
	mkdir $(BUILDDIR)

doxygen:
	doxygen "$(SOURCEDIR)/Doxyfile"

api: doxygen
	doxyphp2sphinx --xml-dir "$(BUILDDIR)/doxygen/xml" "Cob::Bundle::ApiServicesBundle"

plantuml:
	../vendor/bin/plantuml --config config/plantuml.cfg -tsvg -failfast2 -r -o $(IMAGESDIR)/uml/ _uml/*.puml

html: clean init plantuml api

# Catch-all target: route all unknown targets to Sphinx using the new
# "make mode" option.  $(O) is meant as a shortcut for $(SPHINXOPTS).
%: Makefile
	@$(SPHINXBUILD) -M $@ "$(SOURCEDIR)" "$(BUILDDIR)" $(SPHINXOPTS) $(O)