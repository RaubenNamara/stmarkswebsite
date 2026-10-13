import { test, describe } from 'node:test'
import assert from 'node:assert/strict'
import { cleanWordHtml, plainTextToHtml, toArticleHtml } from '../src/utils/articleHtml.ts'

describe('plain text', () => {
  test('blank lines become paragraphs, single line breaks become <br>', () => {
    assert.equal(plainTextToHtml('One.\n\nTwo line a\nTwo line b\n\n\n\nThree.'), '<p>One.</p>\n<p>Two line a<br>Two line b</p>\n<p>Three.</p>')
  })
  test('Windows line endings and stray spaces are handled', () => {
    assert.equal(plainTextToHtml('A\r\n\r\n  B  \r\n'), '<p>A</p>\n<p>B</p>')
  })
  test('nothing typed can become markup', () => {
    const out = plainTextToHtml('<script>alert(1)</script> & <img src=x onerror=alert(1)>')
    assert.ok(!/<script|<img/i.test(out))
    assert.match(out, /&lt;script&gt;alert\(1\)&lt;\/script&gt; &amp; &lt;img/)
  })
  test('empty input gives empty output', () => {
    assert.equal(plainTextToHtml(''), '')
    assert.equal(toArticleHtml(null), '')
    assert.equal(toArticleHtml(undefined), '')
  })
})

describe('Word-pasted HTML', () => {
  const word =
    '<p class="MsoNormal" style="mso-margin-top-alt: auto; line-height: normal;"><strong><span lang="EN-GB" style="font-family: \'Times New Roman\',serif; mso-fareast-font-family: x;">Women of Purpose</span></strong><o:p></o:p></p>' +
    '<p class="MsoNormal">&nbsp;</p>' +
    '<p><span lang="EN-GB">The heroes of today,<br>Like the rising sun</span></p>'
  test('inline styles, Mso classes, language tags and Office tags are removed', () => {
    const out = cleanWordHtml(word)
    assert.ok(!/style=|Mso|lang=|o:p|Times New Roman/.test(out), out)
  })
  test('the structure is kept: bold, line breaks, paragraphs, text', () => {
    const out = cleanWordHtml(word)
    assert.match(out, /<strong>.*Women of Purpose.*<\/strong>/)
    assert.match(out, /The heroes of today,<br>Like the rising sun/)
    assert.equal((out.match(/<p/g) ?? []).length, 2, 'the empty &nbsp; paragraph is dropped')
  })
  test('links and images keep their addresses', () => {
    const out = cleanWordHtml('<p style="x"><a href="/news/a" style="color:red">link</a><img src="/u/a.jpg" style="width:9px"></p>')
    assert.match(out, /<a href="\/news\/a">link<\/a>/)
    assert.match(out, /<img src="\/u\/a\.jpg">/)
  })
})

describe('automatic choice', () => {
  test('HTML is cleaned, plain text is converted', () => {
    assert.equal(toArticleHtml('<p style="a:b">Hi</p>'), '<p>Hi</p>')
    assert.equal(toArticleHtml('Hi\n\nThere'), '<p>Hi</p>\n<p>There</p>')
  })
  test('plain text that merely mentions < and > is not mistaken for HTML', () => {
    assert.equal(toArticleHtml('Scores of 5 < 6 and 7 > 3'), '<p>Scores of 5 &lt; 6 and 7 &gt; 3</p>')
  })
})
