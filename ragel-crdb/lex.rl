package main

import (
	"bytes"
	"fmt"
	"strconv"
	"unicode/utf8"
	
	"github.com/cockroachdb/cockroach/pkg/sql/lex"
)

var _ = fmt.Println
var _ = strconv.Atoi

func lexSQL(data []byte) error {

	%% machine scanner;
	%% write data;

	cs, p, pe, eof := 0, 0, len(data), len(data)
	_ = eof

	var (
		mark int
		s string
		uval uint64
		err error
		isFconst bool
		isUpper bool
		isNotASCII bool
		numQuote int
		b []byte
		ch byte
		rn rune
		buf *bytes.Buffer
	)
	str := func() { s = string(data[mark:p]) }

	%%{
		action mark { mark = p }
		action str { str() }

		action placeholder {
			mark++
			str()
			uval, err = strconv.ParseUint(s, 10, 64)
			if err != nil {
				return err
			}
			if err == nil && uval > 1<<63 {
				return fmt.Errorf("integer value out of range: %d", uval)
			}
			emit(lex.PLACEHOLDER, s)
		}
		action number {
			str()
			if isFconst {
				emit(lex.FCONST, s)
			} else {
				emit(lex.ICONST, s)
			}
			isFconst = false
		}
		action markZero {
			if mark == p && data[p] == '0' {
				mark++
			}
		}
		action fconst {
			isFconst = true
		}
		action hex {
			str()
			emit(lex.ICONST, s)
		}
		action ident {
			if isNotASCII {
				str()
				s = lex.NormalizeName(s)
			} else if isUpper {
				b := make([]byte, p-mark)
				for i, c := range data[mark:p] {
					if c >= 'A' && c <= 'Z' {
						c += 'a' - 'A'
					}
					b[i] = c
				}
				s = string(b)
			} else {
				str()
			}

			if id, ok := lex.Keywords[s]; ok {
				emit(Tok(id.Tok), s)
			} else {
				emit(lex.IDENT, s)
			}
			isUpper = false
			isNotASCII = false
		}
		int = digit+;
		pn = ('-' | '+')?;
		number =
			pn
			(int $markZero ('.' >fconst)? digit* | '.' >fconst int)
			([eE] >fconst pn int)?
			;
		hex = '0x'i xdigit+;
		placeholder = '$' int;
		notASCII = 128..255 >{ isNotASCII = true };
		identStart =
			'a'..'z'
			| 'A'..'Z' >{ isUpper = true }
			| '_'
			| notASCII
			;
		ident =
			identStart
			(identStart | digit | '$')*
			;
		identQuote =
			'"'
			(
				'""' %{ numQuote++ }
				| notASCII
				| /[^"]/
			)*
			'"'
			;
		action identQuote {
			if numQuote != 0 {
				b = make([]byte, p-mark-2-numQuote)
				// Now use numQuote as an index into b.
				numQuote = 0
				for i := mark+1; i < p-1; i++ {
					b[numQuote] = data[i]
					numQuote++
					if data[i] == '"' {
						i++
					}
				}
				s = string(b)
				numQuote = 0
			} else {
				b = data[mark+1:p-1]
			}
			if isNotASCII {
				if !utf8.Valid(b) {
					return fmt.Errorf("invalid UTF-8 string")
				}
				isNotASCII = false
			}
			emit(lex.IDENT, string(b))
		}
		singleQuote =
			"'"
			(
				"''" %{ numQuote++ }
				| notASCII
				| /[^']/
			)*
			"'"
			;
		action singleQuote {
			if numQuote != 0 {
				b = make([]byte, p-mark-2-numQuote)
				// Now use numQuote as an index into b.
				numQuote = 0
				for i := mark+1; i < p-1; i++ {
					b[numQuote] = data[i]
					numQuote++
					if data[i] == '\'' {
						i++
					}
				}
				s = string(b)
				numQuote = 0
			} else {
				b = data[mark+1:p-1]
			}
			if isNotASCII {
				if !utf8.Valid(b) {
					return fmt.Errorf("invalid UTF-8 string")
				}
				isNotASCII = false
			}
			emit(lex.SCONST, string(b))
		}
		escape =
			'a' %{ buf.WriteByte('\a') }
			| 'b' %{ buf.WriteByte('\b') }
			| 'f' %{ buf.WriteByte('\f') }
			| 'n' %{ buf.WriteByte('\n') }
			| 'r' %{ buf.WriteByte('\r') }
			| 't' %{ buf.WriteByte('\t') }
			| 'v' %{ buf.WriteByte('\v') }
			;
		slashHex =
			'x'i xdigit {2}
			>{ ch = 0 }
			${ ch = (ch << 4) | unhex(data[p]) }
			%{ buf.WriteByte(ch) }
			;
		slashUnicode =
			((
				'u' xdigit {4}
				${ rn = (rn << 4) | rune(unhex(data[p])) }
			) | (
				'U' xdigit {8}
				${ rn = (rn << 4) | rune(unhex(data[p])) }
			))
			>{ rn = 0 }
			%{ buf.WriteRune(rn) }
			;
		slashOctal =
			('0'..'7') {3}
			>{ ch = 0 }
			${ ch = (ch << 3) | data[p] - '0' }
			%{ buf.WriteByte(ch) }
			;
		stringLiteral =
			"'" %{ buf = new(bytes.Buffer) }
			(
				(
					"''"
					| /[^'\\]/
				) @{ buf.WriteByte(data[p]) }
				| "\\" (
					escape
					| slashHex
					| slashUnicode
					| slashOctal
					| ^(escape | 'x'i | 'u'i | '0'..'7') ${ buf.WriteByte(data[p]) }
				)
			)*
			"'"
			;
		bytes = "b" stringLiteral;
		action bytes {
			emit(lex.BCONST, buf.String())
		}
		escapedString = "e" stringLiteral;
		action escapedString {
			if !utf8.Valid(buf.Bytes()) {
				return fmt.Errorf("invalid UTF-8 string")
			}
			emit(lex.SCONST, buf.String())
		}
		hexString =
			'x'i %{ buf = new(bytes.Buffer) }
			"'"
			(
				xdigit {2}
				>{ ch = 0 }
				${ ch = (ch << 4) | unhex(data[p]) }
				%{ buf.WriteByte(ch) }
			)*
			"'"
			;
		bitArray =
			"B'"
			(/[01]/)*
			"'"
			;
		action bitArray {
			emit(lex.BITCONST, string(data[mark+2:p-1]))
		}
		top =
			  space
			| /--[^\n]*/
			| hex >mark %hex
			| number >mark %number
			| placeholder >mark %placeholder
			| ident >mark %ident
			| identQuote >mark %identQuote
			| singleQuote >mark %singleQuote
			| bytes %bytes
			| escapedString %escapedString
			| hexString %bytes
			| bitArray >mark %bitArray

			| punct %{ emitToken(Tok(data[p-1])) }

			| '..' %{ emitToken(lex.DOT_DOT) }

			| '!=' %{ emitToken(lex.NOT_EQUALS) }
			| '!~*' %{ emitToken(lex.NOT_REGIMATCH) }
			| '!~' %{ emitToken(lex.NOT_REGMATCH) }

			| '??' %{ emitToken(lex.HELPTOKEN) }
			| '?|' %{ emitToken(lex.JSON_SOME_EXISTS) }
			| '?&' %{ emitToken(lex.JSON_ALL_EXISTS) }

			| '<<=' %{ emitToken(lex.INET_CONTAINED_BY_OR_EQUALS) }
			| '<<' %{ emitToken(lex.LSHIFT) }
			| '<>' %{ emitToken(lex.NOT_EQUALS) }
			| '<=' %{ emitToken(lex.LESS_EQUALS) }
			| '<@' %{ emitToken(lex.CONTAINED_BY) }

			| '>>=' %{ emitToken(lex.INET_CONTAINS_OR_EQUALS) }
			| '>>' %{ emitToken(lex.RSHIFT) }
			| '>=' %{ emitToken(lex.GREATER_EQUALS) }

			| ':::' %{ emitToken(lex.TYPEANNOTATE) }
			| '::' %{ emitToken(lex.TYPECAST) }

			| '||' %{ emitToken(lex.CONCAT) }

			| '//' %{ emitToken(lex.FLOORDIV) }

			| '~*' %{ emitToken(lex.REGIMATCH) }

			| '@>' %{ emitToken(lex.CONTAINS) }

			| '&&' %{ emitToken(lex.INET_CONTAINS_OR_CONTAINED_BY) }

			| '->>' %{ emitToken(lex.FETCHTEXT) }
			| '->' %{ emitToken(lex.FETCHVAL) }

			| '#>>' %{ emitToken(lex.FETCHTEXT_PATH) }
			| '#>' %{ emitToken(lex.FETCHVAL_PATH) }
			| '#-' %{ emitToken(lex.REMOVE_PATH) }

			;
		main :=
			top**
			;

		write init;
		write exec;
	}%%

	return nil
}

func unhex(c byte) byte {
	switch {
	case '0' <= c && c <= '9':
		return c - '0'
	case 'a' <= c && c <= 'f':
		return c - 'a' + 10
	case 'A' <= c && c <= 'F':
		return c - 'A' + 10
	}
	return 0
}
