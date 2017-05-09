# -*- coding: utf-8 -*-
import sys
import re
import codecs


class args:
    """definice tridy pro uchovani vstupnich parametru"""
    def __init__(self):
        self.__kontrola = [0, 0, 0, 0]
        self.input = sys.stdin
        self.output = sys.stdout
        self.format = ""
        self.br = False
        self.nactiarg()

    def nactiarg(self):
        """nacte a nastavi argumenty predane skriptu"""
        jmeno = True
        for x in sys.argv:

            if jmeno:
                jmeno = False
                continue

            elif x == "--help":
                if len(sys.argv) == 2:
                    self.help()
                    sys.exit(0)
                else:
                    sys.exit(1)

            elif x[0:8] == "--input=":
                    self.__kontrola[0] += 1

                    if self.__kontrola[0] == 1:
                        self.input = x[8:]
                    else:
                        sys.stderr.write("argument --input zadan: "+str(self.__kontrola[0])+"x\n")
                        sys.exit(1)

            elif x[0:9] == "--format=":
                self.__kontrola[1] += 1
                if self.__kontrola[1] == 1:
                    self.format = x[9:]
                else:
                    sys.stderr.write("argument --format zadan: "+str(self.__kontrola[1])+"x\n")
                    sys.exit(1)

            elif x[0:9] == "--output=":
                self.__kontrola[2] += 1
                if self.__kontrola[2] == 1:
                    self.output = x[9:]
                else:
                    sys.stderr.write("argument --output zadan: "+str(self.__kontrola[2])+"x\n")
                    sys.exit(1)

            elif x == "--br":
                self.__kontrola[3] += 1
                if self.__kontrola[3] == 1:
                    self.br = True
                else:
                    sys.stderr.write("argument --br zadan: "+str(self.__kontrola[3])+"x\n")
                    sys.exit(1)

            else:
                sys.exit(1)

    def help(self):
        """vypise napovedu"""
        print(
            "Napoveda\n"
            "Mozne parametry:\n"
            "--format=filename   urceni formatovaciho souboru.\n"
            "--input=filename    urceni vstupniho souboru v kodovani UTF-8.\n"
            "--output=filename   urceni vystupniho souboru s naformatovanym vstupnim textem\n"
            "--br                prida element <br /> na konec kazdeho radku puvodniho vstupniho textu")


class soubor:
    """popis tridy, ktera pracuje se soubory"""
    def __init__(self, soubor, typ, formatovaci=False):
        self.soubor = soubor
        self.typ = typ
        self.formatovaci = formatovaci
        self.file = ""
        self.retezec = ""
        self.ERROR = ""  # priznak neotevreni souboru
        self.otevri()

    def otevri(self):
        """otevre soubor"""
        if self.soubor != sys.stdout and self.soubor != sys.stderr and self.soubor != sys.stdin:
            try:
                self.file = codecs.open(self.soubor, self.typ, 'utf-8')
            except EnvironmentError:
                if not self.formatovaci:
                    sys.stderr.write("soubor '"+str(self.soubor)+"' nelze otevrit\n")
                    if self.typ == "w":
                        sys.exit(3)
                    else:
                        sys.exit(2)
                else:
                    self.ERROR = "konci"
                    return
        else:
            self.file = self.soubor

        if self.typ == "r":
            self.nactiznaky()

    def zavri(self):
        """uzavre otevreny soubor"""
        try:
            self.file.close()
        except EnvironmentError:
            sys.stderr.write("soubor '"+str(self.soubor)+"' nelze zavrit\n")
            sys.exit(2)

    def nactiznaky(self):
        """nacte retezec ze souboru a soubor uzavre"""
        if self.soubor == sys.stdin:
            self.retezec = sys.stdin.read()
        else:
            self.retezec = self.file.read()
            self.zavri

    def zapis(self, string):
        """zapise do souboru a uzavre soubor"""
        self.file.write(str(string))
        self.zavri


class algor:
    """trida tvorici novy vystup pro program"""
    def __init__(self, format, vstup, br=False):
        self.format = format
        self.vstup = vstup
        self.pravidla = []
        self.br = br
        self.vystup = ""
        self.zacni()

    def zacni(self):
        """provedeni funkci"""
        self.zpracujformat()
        self.zkontroluj()
        self.zmenreg()
        self.najdipozice()
        self.generuj()

    def zpracujformat(self):
        """rozdeli soubor format do pole"""
        for line in self.format.splitlines():
            if line == "":
                continue

            tt = []
            pom3 = []
            rozdel = line.partition("\t")
            if rozdel[1] != "\t":

                sys.exit(4)

            tt.append(rozdel[0])

            pom = rozdel[2].split("\t")
            for a in pom:
                pom1 = a.split(" ")
                for b in pom1:
                    pom2 = b.split(",")
                    for c in pom2:
                        if c != "" and c not in pom3:
                            pom3.append(c)

            tt.append(pom3)
            self.pravidla.append(tt)

    def zkontroluj(self):
        """kontrola formatovacich prikazu a zmena na tagy (HTML znacky)"""
        for p in range(len(self.pravidla)):
            for a in range(len(self.pravidla[p][1])):
                S1892 = self.pravidla[p][1][a]
                if S1892 == "bold":
                    tmp = ['<b>', '</b>']
                elif S1892 == "italic":
                    tmp = ['<i>', '</i>']
                elif S1892 == "underline":
                    tmp = ['<u>', '</u>']
                elif S1892 == "teletype":
                    tmp = ['<tt>', '</tt>']
                elif S1892[0:5] == "size:":
                    if not re.match('[1-7]', S1892[5:]):
                        sys.stderr.write("format '"+str(S1892)+"' je chybny\n")
                        sys.exit(4)
                    tmp = ['<font size='+S1892[5:]+'>', '</font>']
                elif S1892[0:6] == "color:":
                    if not re.match('[0-9a-fA-F]{6}', S1892[6:]):
                        sys.stderr.write("format '"+str(S1892)+"' je chybny\n")
                        sys.exit(4)
                    tmp = ['<font color=#'+S1892[6:]+'>', '</font>']
                else:
                    sys.exit(4)

                self.pravidla[p][1][a] = tmp

    def zmenreg(self):
        """zmeni regex formatovaciho souboru na regex pythonu"""
        for n in range(len(self.pravidla)):
            a = self.pravidla[n][0]
            if a == '':
                sys.exit(4)

            neg = ''
            regex = ""
            stav = 0
            zavorky = 0
            procento = False

            if a[0] in ".|*+)":
                sys.exit(4)

            for i in range(len(a)):
                if ord(a[i]) < 32 or zavorky < 0:
                    sys.exit(4)

                # 0)
                if stav == 0:

                    if a[i] == "%":
                        if i == len(a)-1:
                            sys.exit(4)
                        stav = 1

                    elif a[i] == "!":
                        if i == len(a)-1:
                            sys.exit(4)

                        if neg == '^':
                            neg = ''
                        else:
                            neg = '^'

                    elif a[i] == ".":
                        if (a[i-1] in ".|!(" and not procento) or i == len(a)-1:
                            sys.exit(4)
                        procento = False
                        continue

                    elif a[i] == "(":
                        regex += a[i]
                        zavorky += 1

                    elif a[i] == ")":
                        if a[i-1] in "(!|." and not procento:
                            sys.exit(4)
                        regex += a[i]
                        zavorky -= 1

                    elif a[i] in "*+":
                        if a[i-1] in ".|!(" and not procento:
                            sys.exit(4)
                        if a[i-1] in "*+":
                            continue
                        regex += a[i]

                    elif a[i] == "|":
                        if (a[i-1] in "!|.(" and not procento) or i == len(a)-1:
                            sys.exit(4)
                        regex += a[i]

                    else:
                        if a[i] in '\\^[]{}$?':
                            regex += '\\'

                        if neg == '^':
                            regex += '['+neg+a[i]+']'
                            neg = ''
                        else:
                            regex += a[i]

                    procento = False

                # 1)
                elif stav == 1:
                    # %znak
                    if a[i] == "s":
                        regex += '['+neg+' \t\n\r\f\v]'

                    elif a[i] == "a":
                        if neg == '^':
                            regex += '^'
                        else:
                            regex += '.'
                    elif a[i] == "d":
                        regex += '['+neg+'0-9]'
                    elif a[i] == "l":
                        regex += '['+neg+'a-z]'
                    elif a[i] == "L":
                        regex += '['+neg+'A-Z]'
                    elif a[i] == "w":
                        regex += '['+neg+'a-zA-Z]'
                    elif a[i] == "W":
                        regex += '['+neg+'0-9a-zA-Z]'
                    elif a[i] == "t":
                        regex += '['+neg+'\t]'
                    elif a[i] == "n":
                        regex += '['+neg+'\n]'
                    elif a[i] in '.|!*+()%':
                        regex += '['+neg+'\\'+a[i]+']'
                    else:
                        sys.exit(4)

                    stav = 0
                    neg = ''
                    procento = True

            if zavorky != 0:
                sys.exit(4)

            self.pravidla[n][0] = regex

    def najdipozice(self):
        """najde pozice na kterych budou vlozeny tagy a ulozi je do pole"""
        j = 0
        for a in self.pravidla:
            pom = []
            for match in re.finditer(a[0], self.vstup, re.DOTALL):
                if match.start() != match.end():
                    pom.append(match.span())
            self.pravidla[j].append(pom)
            j = j + 1

    def generuj(self):
        """generuje vystupni retezec. tzn kombinuje vstup a tagy"""
        for a in range(len(self.vstup)):

            for x in self.pravidla:
                for y in x[2]:
                    if y[0] == a:
                        for z in x[1]:
                            self.vystup += z[0]

            self.vystup += self.vstup[a]

            tt = []
            for x in self.pravidla:
                for y in x[2]:
                    if y[1]-1 == a:
                        for z in x[1]:
                            tt.insert(0, z[1])
            self.vystup += "".join(tt)


def vlozbr(br, string):
    """pokud argument --br vlozi pred kazdy novy radek <br />"""
    if br:
        string = re.sub('\n', '<br />\n', string)
            # self.vystup = self.vystup[:-1]
    return string


if __name__ == '__main__':
    """hlavni fce"""
    Argumenty = args()
    vstup = soubor(Argumenty.input, "r")
    format = soubor(Argumenty.format, "r", True)
    vystup = soubor(Argumenty.output, "w")
    if format.ERROR or not format.retezec:
        vysl = vlozbr(Argumenty.br, vstup.retezec)
        vystup.zapis(vysl)
        sys.exit(0)

    program = algor(format.retezec, vstup.retezec)
    vysl = vlozbr(Argumenty.br, program.vystup)
    vystup.zapis(vysl)
