<?php
/*******************************************************************************
 * Copyright notice
 *
 * Copyright 2013 Sven-S. Porst, Göttingen State and University Library
 *                <porst@sub.uni-goettingen.de>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 ******************************************************************************/

namespace Subugoe\Find\ViewHelpers\LinkedData\Renderer;



/*
 * http://www.w3.org/RDF/
 * http://www.w3.org/TR/REC-rdf-syntax/
 */
class RDFRenderer extends AbstractRenderer {

	public function renderItems ($items) {
		$doc = new \DomDocument();
		$this->prefixes['rdf'] = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
		$rdf = $doc->createElement($this->prefixedName('rdf:RDF'));
		$doc->appendChild($rdf);

		// loop over subjects
		foreach ($items as $subjectURI => $subjectStatements) {
			$subjectDescription = $doc->createElement($this->prefixedName('rdf:Description'));
			$subjectDescription->setAttribute($this->prefixedName('rdf:about'), $this->prefixedName($subjectURI, TRUE));

			// loop over predicates
			foreach ($subjectStatements as $predicate => $objects) {

				// loop over objects
				foreach ($objects as $object => $properties) {
					$predicateElement = $doc->createElement($this->prefixedName($predicate));
					$subjectDescription->appendChild($predicateElement);

					if ($properties === NULL) {
						$objectParts = explode(':', $object, 2);
						if ($this->prefixes[$objectParts[0]] && count($objectParts) === 2) {
							$object = $this->prefixes[$objectParts[0]] . $objectParts[1];
						}
						$predicateElement->setAttribute($this->prefixedName('rdf:resource'), $this->prefixedName($object, TRUE));
					}
					else {
						if ($properties['language']) {
							$predicateElement->setAttribute($this->prefixedName('xml:lang'), $properties['language']);
						}
						if ($properties['type']) {
							$predicateElement->setAttribute($this->prefixedName('rdf:datatype'), $this->prefixedName($properties['type'], TRUE));
						}

						$predicateElement->appendChild($doc->createTextNode($object));
					}

					$subjectDescription->appendChild($predicateElement);
				}
			}

			$rdf->appendChild($subjectDescription);
		}

		// Add the prefixes that are used as xmlns.
		foreach (array_keys($this->usedPrefixes) as $prefix) {
			if ($this->prefixes[$prefix]) {
				$doc->firstChild->setAttribute('xmlns:' . $prefix, $this->prefixes[$prefix]);
			}
		}

		$doc->formatOutput = TRUE;
		return $doc->saveXML();
	}


	
	private function prefixedName ($name, $expand = FALSE) {
		$nameParts = explode(':', $name, 2);
		if ($this->prefixes[$nameParts[0]]) {
			$this->usedPrefixes[$nameParts[0]] = TRUE;
			if ($expand && count($nameParts) > 1) {
				$name = $this->prefixes[$nameParts[0]] . $nameParts[1];
			}
		}

		return $name;
	}

}

?>