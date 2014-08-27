////////////////////////////////////////////////////////////////////////////////////////
// Big Integer Library v. 5.4
// Created 2000, last modified 2009
// Leemon Baird
// www.leemon.com
//
// Version history:
// v 5.4  3 Oct 2009
//   - added "var i" to greaterShift() so i is not global. (Thanks to PÅ½ter Szabâ€” for finding that bug)
//
// v 5.3  21 Sep 2009
//   - added randProbPrime(k) for probable primes
//   - unrolled loop in mont_ (slightly faster)
//   - millerRabin now takes a bigInt parameter rather than an int
//
// v 5.2  15 Sep 2009
//   - fixed capitalization in call to int2bigInt in randBigInt
//     (thanks to Emili Evripidou, Reinhold Behringer, and Samuel Macaleese for finding that bug)
//
// v 5.1  8 Oct 2007 
//   - renamed inverseModInt_ to inverseModInt since it doesn't change its parameters
//   - added functions GCD and randBigInt, which call GCD_ and randBigInt_
//   - fixed a bug found by Rob Visser (see comment with his name below)
//   - improved comments
//
// This file is public domain.   You can use it for any purpose without restriction.
// I do not guarantee that it is correct, so use it at your own risk.  If you use 
// it for something interesting, I'd appreciate hearing about it.  If you find 
// any bugs or make any improvements, I'd appreciate hearing about those too.
// It would also be nice if my name and URL were left in the comments.  But none 
// of that is required.
//
// This code defines a bigInt library for arbitrary-precision integers.
// A bigInt is an array of integers storing the value in chunks of bpe bits, 
// little endian (buff[0] is the least significant word).
// Negative bigInts are stored two's complement.  Almost all the functions treat
// bigInts as nonnegative.  The few that view them as two's complement say so
// in their comments.  Some functions assume their parameters have at least one 
// leading zero element. Functions with an underscore at the end of the name put
// their answer into one of the arrays passed in, and have unpredictable behavior 
// in case of overflow, so the caller must make sure the arrays are big enough to 
// hold the answer.  But the average user should never have to call any of the 
// underscored functions.  Each important underscored function has a wrapper function 
// of the same name without the underscore that takes care of the details for you.  
// For each underscored function where a parameter is modified, that same variable 
// must not be used as another argument too.  So, you cannot square x by doing 
// multMod_(x,x,n).  You must use squareMod_(x,n) instead, or do y=dup(x); multMod_(x,y,n).
// Or simply use the multMod(x,x,n) function without the underscore, where
// such issues never arise, because non-underscored functions never change
// their parameters; they always allocate new memory for the answer that is returned.
//
// These functions are designed to avoid frequent dynamic memory allocation in the inner loop.
// For most functions, if it needs a BigInt as a local variable it will actually use
// a global, and will only allocate to it only when it's not the right size.  This ensures
// that when a function is called repeatedly with same-sized parameters, it only allocates
// memory on the first call.
//
// Note that for cryptographic purposes, the calls to Math.random() must 
// be replaced with calls to a better pseudorandom number generator.
//
// In the following, "bigInt" means a bigInt with at least one leading zero element,
// and "integer" means a nonnegative integer less than radix.  In some cases, integer 
// can be negative.  Negative bigInts are 2s complement.
// 
// The following functions do not modify their inputs.
// Those returning a bigInt, string, or Array will dynamically allocate memory for that value.
// Those returning a boolean will return the integer 0 (false) or 1 (true).
// Those returning boolean or int will not allocate memory except possibly on the first 
// time they're called with a given parameter size.
// 
// bigInt  add(x,y)               //return (x+y) for bigInts x and y.  
// bigInt  addInt(x,n)            //return (x+n) where x is a bigInt and n is an integer.
// string  bigInt2str(x,base)     //return a string form of bigInt x in a given base, with 2 <= base <= 95
// int     bitSize(x)             //return how many bits long the bigInt x is, not counting leading zeros
// bigInt  dup(x)                 //return a copy of bigInt x
// boolean equals(x,y)            //is the bigInt x equal to the bigint y?
// boolean equalsInt(x,y)         //is bigint x equal to integer y?
// bigInt  expand(x,n)            //return a copy of x with at least n elements, adding leading zeros if needed
// Array   findPrimes(n)          //return array of all primes less than integer n
// bigInt  GCD(x,y)               //return greatest common divisor of bigInts x and y (each with same number of elements).
// boolean greater(x,y)           //is x>y?  (x and y are nonnegative bigInts)
// boolean greaterShift(x,y,shift)//is (x <<(shift*bpe)) > y?
// bigInt  int2bigInt(t,n,m)      //return a bigInt equal to integer t, with at least n bits and m array elements
// bigInt  inverseMod(x,n)        //return (x**(-1) mod n) for bigInts x and n.  If no inverse exists, it returns null
// int     inverseModInt(x,n)     //return x**(-1) mod n, for integers x and n.  Return 0 if there is no inverse
// boolean isZero(x)              //is the bigInt x equal to zero?
// boolean millerRabin(x,b)       //does one round of Miller-Rabin base integer b say that bigInt x is possibly prime? (b is bigInt, 1<b<x)
// boolean millerRabinInt(x,b)    //does one round of Miller-Rabin base integer b say that bigInt x is possibly prime? (b is int,    1<b<x)
// bigInt  mod(x,n)               //return a new bigInt equal to (x mod n) for bigInts x and n.
// int     modInt(x,n)            //return x mod n for bigInt x and integer n.
// bigInt  mult(x,y)              //return x*y for bigInts x and y. This is faster when y<x.
// bigInt  multMod(x,y,n)         //return (x*y mod n) for bigInts x,y,n.  For greater speed, let y<x.
// boolean negative(x)            //is bigInt x negative?
// bigInt  powMod(x,y,n)          //return (x**y mod n) where x,y,n are bigInts and ** is exponentiation.  0**0=1. Faster for odd n.
// bigInt  randBigInt(n,s)        //return an n-bit random BigInt (n>=1).  If s=1, then the most significant of those n bits is set to 1.
// bigInt  randTruePrime(k)       //return a new, random, k-bit, true prime bigInt using Maurer's algorithm.
// bigInt  randProbPrime(k)       //return a new, random, k-bit, probable prime bigInt (probability it's composite less than 2^-80).
// bigInt  str2bigInt(s,b,n,m)    //return a bigInt for number represented in string s in base b with at least n bits and m array elements
// bigInt  sub(x,y)               //return (x-y) for bigInts x and y.  Negative answers will be 2s complement
// bigInt  trim(x,k)              //return a copy of x with exactly k leading zero elements
//
//
// The following functions each have a non-underscored version, which most users should call instead.
// These functions each write to a single parameter, and the caller is responsible for ensuring the array 
// passed in is large enough to hold the result. 
//
// void    addInt_(x,n)          //do x=x+n where x is a bigInt and n is an integer
// void    add_(x,y)             //do x=x+y for bigInts x and y
// void    copy_(x,y)            //do x=y on bigInts x and y
// void    copyInt_(x,n)         //do x=n on bigInt x and integer n
// void    GCD_(x,y)             //set x to the greatest common divisor of bigInts x and y, (y is destroyed).  (This never overflows its array).
// boolean inverseMod_(x,n)      //do x=x**(-1) mod n, for bigInts x and n. Returns 1 (0) if inverse does (doesn't) exist
// void    mod_(x,n)             //do x=x mod n for bigInts x and n. (This never overflows its array).
// void    mult_(x,y)            //do x=x*y for bigInts x and y.
// void    multMod_(x,y,n)       //do x=x*y  mod n for bigInts x,y,n.
// void    powMod_(x,y,n)        //do x=x**y mod n, where x,y,n are bigInts (n is odd) and ** is exponentiation.  0**0=1.
// void    randBigInt_(b,n,s)    //do b = an n-bit random BigInt. if s=1, then nth bit (most significant bit) is set to 1. n>=1.
// void    randTruePrime_(ans,k) //do ans = a random k-bit true random prime (not just probable prime) with 1 in the msb.
// void    sub_(x,y)             //do x=x-y for bigInts x and y. Negative answers will be 2s complement.
//
// The following functions do NOT have a non-underscored version. 
// They each write a bigInt result to one or more parameters.  The caller is responsible for
// ensuring the arrays passed in are large enough to hold the results. 
//
// void addShift_(x,y,ys)       //do x=x+(y<<(ys*bpe))
// void carry_(x)               //do carries and borrows so each element of the bigInt x fits in bpe bits.
// void divide_(x,y,q,r)        //divide x by y giving quotient q and remainder r
// int  divInt_(x,n)            //do x=floor(x/n) for bigInt x and integer n, and return the remainder. (This never overflows its array).
// int  eGCD_(x,y,d,a,b)        //sets a,b,d to positive bigInts such that d = GCD_(x,y) = a*x-b*y
// void halve_(x)               //do x=floor(|x|/2)*sgn(x) for bigInt x in 2's complement.  (This never overflows its array).
// void leftShift_(x,n)         //left shift bigInt x by n bits.  n<bpe.
// void linComb_(x,y,a,b)       //do x=a*x+b*y for bigInts x and y and integers a and b
// void linCombShift_(x,y,b,ys) //do x=x+b*(y<<(ys*bpe)) for bigInts x and y, and integers b and ys
// void mont_(x,y,n,np)         //Montgomery multiplication (see comments where the function is defined)
// void multInt_(x,n)           //do x=x*n where x is a bigInt and n is an integer.
// void rightShift_(x,n)        //right shift bigInt x by n bits.  0 <= n < bpe. (This never overflows its array).
// void squareMod_(x,n)         //do x=x*x  mod n for bigInts x,n
// void subShift_(x,y,ys)       //do x=x-(y<<(ys*bpe)). Negative answers will be 2s complement.
//
// The following functions are based on algorithms from the _Handbook of Applied Cryptography_
//    powMod_()           = algorithm 14.94, Montgomery exponentiation
//    eGCD_,inverseMod_() = algorithm 14.61, Binary extended GCD_
//    GCD_()              = algorothm 14.57, Lehmer's algorithm
//    mont_()             = algorithm 14.36, Montgomery multiplication
//    divide_()           = algorithm 14.20  Multiple-precision division
//    squareMod_()        = algorithm 14.16  Multiple-precision squaring
//    randTruePrime_()    = algorithm  4.62, Maurer's algorithm
//    millerRabin()       = algorithm  4.24, Miller-Rabin algorithm
//
// Profiling shows:
//     randTruePrime_() spends:
//         10% of its time in calls to powMod_()
//         85% of its time in calls to millerRabin()
//     millerRabin() spends:
//         99% of its time in calls to powMod_()   (always with a base of 2)
//     powMod_() spends:
//         94% of its time in calls to mont_()  (almost always with x==y)
//
// This suggests there are several ways to speed up this library slightly:
//     - convert powMod_ to use a Montgomery form of k-ary window (or maybe a Montgomery form of sliding window)
//         -- this should especially focus on being fast when raising 2 to a power mod n
//     - convert randTruePrime_() to use a minimum r of 1/3 instead of 1/2 with the appropriate change to the test
//     - tune the parameters in randTruePrime_(), including c, m, and recLimit
//     - speed up the single loop in mont_() that takes 95% of the runtime, perhaps by reducing checking
//       within the loop when all the parameters are the same length.
//
// There are several ideas that look like they wouldn't help much at all:
//     - replacing trial division in randTruePrime_() with a sieve (that speeds up something taking almost no time anyway)
//     - increase bpe from 15 to 30 (that would help if we had a 32*32->64 multiplier, but not with JavaScript's 32*32->32)
//     - speeding up mont_(x,y,n,np) when x==y by doing a non-modular, non-Montgomery square
//       followed by a Montgomery reduction.  The intermediate answer will be twice as long as x, so that
//       method would be slower.  This is unfortunate because the code currently spends almost all of its time
//       doing mont_(x,x,...), both for randTruePrime_() and powMod_().  A faster method for Montgomery squaring
//       would have a large impact on the speed of randTruePrime_() and powMod_().  HAC has a couple of poorly-worded
//       sentences that seem to imply it's faster to do a non-modular square followed by a single
//       Montgomery reduction, but that's obviously wrong.
////////////////////////////////////////////////////////////////////////////////////////
function findPrimes(e){var t,n,r,i;n=new Array(e);for(t=0;t<e;t++)n[t]=0;n[0]=2;r=0;for(;n[r]<e;){for(t=n[r]*n[r];t<e;t+=n[r])n[t]=1;r++;n[r]=n[r-1]+1;for(;n[r]<e&&n[n[r]];n[r]++);}i=new Array(r);for(t=0;t<r;t++)i[t]=n[t];return i}function millerRabinInt(e,t){if(mr_x1.length!=e.length){mr_x1=dup(e);mr_r=dup(e);mr_a=dup(e)}copyInt_(mr_a,t);return millerRabin(e,mr_a)}function millerRabin(e,t){var n,r,i,s;if(mr_x1.length!=e.length){mr_x1=dup(e);mr_r=dup(e);mr_a=dup(e)}copy_(mr_a,t);copy_(mr_r,e);copy_(mr_x1,e);addInt_(mr_r,-1);addInt_(mr_x1,-1);i=0;for(n=0;n<mr_r.length;n++)for(r=1;r<mask;r<<=1)if(e[n]&r){s=i<mr_r.length+bpe?i:0;n=mr_r.length;r=mask}else i++;if(s)rightShift_(mr_r,s);powMod_(mr_a,mr_r,e);if(!equalsInt(mr_a,1)&&!equals(mr_a,mr_x1)){r=1;while(r<=s-1&&!equals(mr_a,mr_x1)){squareMod_(mr_a,e);if(equalsInt(mr_a,1)){return 0}r++}if(!equals(mr_a,mr_x1)){return 0}}return 1}function bitSize(e){var t,n,r;for(t=e.length-1;e[t]==0&&t>0;t--);for(n=0,r=e[t];r;r>>=1,n++);n+=bpe*t;return n}function expand(e,t){var n=int2bigInt(0,(e.length>t?e.length:t)*bpe,0);copy_(n,e);return n}function randTruePrime(e){var t=int2bigInt(0,e,0);randTruePrime_(t,e);return trim(t,1)}function randProbPrime(e){if(e>=600)return randProbPrimeRounds(e,2);if(e>=550)return randProbPrimeRounds(e,4);if(e>=500)return randProbPrimeRounds(e,5);if(e>=400)return randProbPrimeRounds(e,6);if(e>=350)return randProbPrimeRounds(e,7);if(e>=300)return randProbPrimeRounds(e,9);if(e>=250)return randProbPrimeRounds(e,12);if(e>=200)return randProbPrimeRounds(e,15);if(e>=150)return randProbPrimeRounds(e,18);if(e>=100)return randProbPrimeRounds(e,27);return randProbPrimeRounds(e,40)}function randProbPrimeRounds(e,t){var n,r,i,s;s=3e4;n=int2bigInt(0,e,0);if(primes.length==0)primes=findPrimes(3e4);if(rpprb.length!=n.length)rpprb=dup(n);for(;;){randBigInt_(n,e,0);n[0]|=1;i=0;for(r=0;r<primes.length&&primes[r]<=s;r++)if(modInt(n,primes[r])==0&&!equalsInt(n,primes[r])){i=1;break}for(r=0;r<t&&!i;r++){randBigInt_(rpprb,e,0);while(!greater(n,rpprb))randBigInt_(rpprb,e,0);if(!millerRabin(n,rpprb))i=1}if(!i)return n}}function mod(e,t){var n=dup(e);mod_(n,t);return trim(n,1)}function addInt(e,t){var n=expand(e,e.length+1);addInt_(n,t);return trim(n,1)}function mult(e,t){var n=expand(e,e.length+t.length);mult_(n,t);return trim(n,1)}function powMod(e,t,n){var r=expand(e,n.length);powMod_(r,trim(t,2),trim(n,2),0);return trim(r,1)}function sub(e,t){var n=expand(e,e.length>t.length?e.length+1:t.length+1);sub_(n,t);return trim(n,1)}function add(e,t){var n=expand(e,e.length>t.length?e.length+1:t.length+1);add_(n,t);return trim(n,1)}function inverseMod(e,t){var n=expand(e,t.length);var r;r=inverseMod_(n,t);return r?trim(n,1):null}function multMod(e,t,n){var r=expand(e,n.length);multMod_(r,t,n);return trim(r,1)}function randTruePrime_(e,t){var n,r,i,s,o,u,a,f,l,c,h;if(primes.length==0)primes=findPrimes(3e4);if(pows.length==0){pows=new Array(512);for(o=0;o<512;o++){pows[o]=Math.pow(2,o/511-1)}}n=.1;r=20;recLimit=20;if(s_i2.length!=e.length){s_i2=dup(e);s_R=dup(e);s_n1=dup(e);s_r2=dup(e);s_d=dup(e);s_x1=dup(e);s_x2=dup(e);s_b=dup(e);s_n=dup(e);s_i=dup(e);s_rm=dup(e);s_q=dup(e);s_a=dup(e);s_aa=dup(e)}if(t<=recLimit){i=(1<<(t+2>>1))-1;copyInt_(e,0);for(s=1;s;){s=0;e[0]=1|1<<t-1|Math.floor(Math.random()*(1<<t));for(o=1;o<primes.length&&(primes[o]&i)==primes[o];o++){if(0==e[0]%primes[o]){s=1;break}}}carry_(e);return}a=n*t*t;if(t>2*r)for(u=1;t-t*u<=r;)u=pows[Math.floor(Math.random()*512)];else u=.5;h=Math.floor(u*t)+1;randTruePrime_(s_q,h);copyInt_(s_i2,0);s_i2[Math.floor((t-2)/bpe)]|=1<<(t-2)%bpe;divide_(s_i2,s_q,s_i,s_rm);l=bitSize(s_i);for(;;){for(;;){randBigInt_(s_R,l,0);if(greater(s_i,s_R))break}addInt_(s_R,1);add_(s_R,s_i);copy_(s_n,s_q);mult_(s_n,s_R);multInt_(s_n,2);addInt_(s_n,1);copy_(s_r2,s_R);multInt_(s_r2,2);for(f=0,o=0;o<primes.length&&primes[o]<a;o++)if(modInt(s_n,primes[o])==0&&!equalsInt(s_n,primes[o])){f=1;break}if(!f)if(!millerRabinInt(s_n,2))f=1;if(!f){addInt_(s_n,-3);for(o=s_n.length-1;s_n[o]==0&&o>0;o--);for(c=0,w=s_n[o];w;w>>=1,c++);c+=bpe*o;for(;;){randBigInt_(s_a,c,0);if(greater(s_n,s_a))break}addInt_(s_n,3);addInt_(s_a,2);copy_(s_b,s_a);copy_(s_n1,s_n);addInt_(s_n1,-1);powMod_(s_b,s_n1,s_n);addInt_(s_b,-1);if(isZero(s_b)){copy_(s_b,s_a);powMod_(s_b,s_r2,s_n);addInt_(s_b,-1);copy_(s_aa,s_n);copy_(s_d,s_b);GCD_(s_d,s_n);if(equalsInt(s_d,1)){copy_(e,s_aa);return}}}}}function randBigInt(e,t){var n,r;n=Math.floor((e-1)/bpe)+2;r=int2bigInt(0,0,n);randBigInt_(r,e,t);return r}function randBigInt_(e,t,n){var r,i;for(r=0;r<e.length;r++)e[r]=0;i=Math.floor((t-1)/bpe)+1;for(r=0;r<i;r++){e[r]=Math.floor(Math.random()*(1<<bpe-1))}e[i-1]&=(2<<(t-1)%bpe)-1;if(n==1)e[i-1]|=1<<(t-1)%bpe}function GCD(e,t){var n,r;n=dup(e);r=dup(t);GCD_(n,r);return n}function GCD_(e,n){var r,i,s,o,u,a,f,l,c;if(T.length!=e.length)T=dup(e);c=1;while(c){c=0;for(r=1;r<n.length;r++)if(n[r]){c=1;break}if(!c)break;for(r=e.length;!e[r]&&r>=0;r--);i=e[r];s=n[r];o=1;u=0;a=0;f=1;while(s+a&&s+f){l=Math.floor((i+o)/(s+a));qp=Math.floor((i+u)/(s+f));if(l!=qp)break;t=o-l*a;o=a;a=t;t=u-l*f;u=f;f=t;t=i-l*s;i=s;s=t}if(u){copy_(T,e);linComb_(e,n,o,u);linComb_(n,T,f,a)}else{mod_(e,n);copy_(T,e);copy_(e,n);copy_(n,T)}}if(n[0]==0)return;t=modInt(e,n[0]);copyInt_(e,n[0]);n[0]=t;while(n[0]){e[0]%=n[0];t=e[0];e[0]=n[0];n[0]=t}}function inverseMod_(e,t){var n=1+2*Math.max(e.length,t.length);if(!(e[0]&1)&&!(t[0]&1)){copyInt_(e,0);return 0}if(eg_u.length!=n){eg_u=new Array(n);eg_v=new Array(n);eg_A=new Array(n);eg_B=new Array(n);eg_C=new Array(n);eg_D=new Array(n)}copy_(eg_u,e);copy_(eg_v,t);copyInt_(eg_A,1);copyInt_(eg_B,0);copyInt_(eg_C,0);copyInt_(eg_D,1);for(;;){while(!(eg_u[0]&1)){halve_(eg_u);if(!(eg_A[0]&1)&&!(eg_B[0]&1)){halve_(eg_A);halve_(eg_B)}else{add_(eg_A,t);halve_(eg_A);sub_(eg_B,e);halve_(eg_B)}}while(!(eg_v[0]&1)){halve_(eg_v);if(!(eg_C[0]&1)&&!(eg_D[0]&1)){halve_(eg_C);halve_(eg_D)}else{add_(eg_C,t);halve_(eg_C);sub_(eg_D,e);halve_(eg_D)}}if(!greater(eg_v,eg_u)){sub_(eg_u,eg_v);sub_(eg_A,eg_C);sub_(eg_B,eg_D)}else{sub_(eg_v,eg_u);sub_(eg_C,eg_A);sub_(eg_D,eg_B)}if(equalsInt(eg_u,0)){if(negative(eg_C))add_(eg_C,t);copy_(e,eg_C);if(!equalsInt(eg_v,1)){copyInt_(e,0);return 0}return 1}}}function inverseModInt(e,t){var n=1,r=0,i;for(;;){if(e==1)return n;if(e==0)return 0;r-=n*Math.floor(t/e);t%=e;if(t==1)return r;if(t==0)return 0;n-=r*Math.floor(e/t);e%=t}}function inverseModInt_(e,t){return inverseModInt(e,t)}function eGCD_(e,t,n,r,i){var s=0;var o=Math.max(e.length,t.length);if(eg_u.length!=o){eg_u=new Array(o);eg_A=new Array(o);eg_B=new Array(o);eg_C=new Array(o);eg_D=new Array(o)}while(!(e[0]&1)&&!(t[0]&1)){halve_(e);halve_(t);s++}copy_(eg_u,e);copy_(n,t);copyInt_(eg_A,1);copyInt_(eg_B,0);copyInt_(eg_C,0);copyInt_(eg_D,1);for(;;){while(!(eg_u[0]&1)){halve_(eg_u);if(!(eg_A[0]&1)&&!(eg_B[0]&1)){halve_(eg_A);halve_(eg_B)}else{add_(eg_A,t);halve_(eg_A);sub_(eg_B,e);halve_(eg_B)}}while(!(n[0]&1)){halve_(n);if(!(eg_C[0]&1)&&!(eg_D[0]&1)){halve_(eg_C);halve_(eg_D)}else{add_(eg_C,t);halve_(eg_C);sub_(eg_D,e);halve_(eg_D)}}if(!greater(n,eg_u)){sub_(eg_u,n);sub_(eg_A,eg_C);sub_(eg_B,eg_D)}else{sub_(n,eg_u);sub_(eg_C,eg_A);sub_(eg_D,eg_B)}if(equalsInt(eg_u,0)){if(negative(eg_C)){add_(eg_C,t);sub_(eg_D,e)}multInt_(eg_D,-1);copy_(r,eg_C);copy_(i,eg_D);leftShift_(n,s);return}}}function negative(e){return e[e.length-1]>>bpe-1&1}function greaterShift(e,t,n){var r,i=e.length,s=t.length;k=i+n<s?i+n:s;for(r=s-1-n;r<i&&r>=0;r++)if(e[r]>0)return 1;for(r=i-1+n;r<s;r++)if(t[r]>0)return 0;for(r=k-1;r>=n;r--)if(e[r-n]>t[r])return 1;else if(e[r-n]<t[r])return 0;return 0}function greater(e,t){var n;var r=e.length<t.length?e.length:t.length;for(n=e.length;n<t.length;n++)if(t[n])return 0;for(n=t.length;n<e.length;n++)if(e[n])return 1;for(n=r-1;n>=0;n--)if(e[n]>t[n])return 1;else if(e[n]<t[n])return 0;return 0}function divide_(e,t,n,r){var i,s;var o,u,a,f,l,c,h;copy_(r,e);for(s=t.length;t[s-1]==0;s--);h=t[s-1];for(c=0;h;c++)h>>=1;c=bpe-c;leftShift_(t,c);leftShift_(r,c);for(i=r.length;r[i-1]==0&&i>s;i--);copyInt_(n,0);while(!greaterShift(t,r,i-s)){subShift_(r,t,i-s);n[i-s]++}for(o=i-1;o>=s;o--){if(r[o]==t[s-1])n[o-s]=mask;else n[o-s]=Math.floor((r[o]*radix+r[o-1])/t[s-1]);for(;;){f=(s>1?t[s-2]:0)*n[o-s];l=f>>bpe;f=f&mask;a=l+n[o-s]*t[s-1];l=a>>bpe;a=a&mask;if(l==r[o]?a==r[o-1]?f>(o>1?r[o-2]:0):a>r[o-1]:l>r[o])n[o-s]--;else break}linCombShift_(r,t,-n[o-s],o-s);if(negative(r)){addShift_(r,t,o-s);n[o-s]--}}rightShift_(t,c);rightShift_(r,c)}function carry_(e){var t,n,r,i;n=e.length;r=0;for(t=0;t<n;t++){r+=e[t];i=0;if(r<0){i=-(r>>bpe);r+=i*radix}e[t]=r&mask;r=(r>>bpe)-i}}function modInt(e,t){var n,r=0;for(n=e.length-1;n>=0;n--)r=(r*radix+e[n])%t;return r}function int2bigInt(e,t,n){var r,i;i=Math.ceil(t/bpe)+1;i=n>i?n:i;buff=new Array(i);copyInt_(buff,e);return buff}function str2bigInt(e,t,n){var r,i,s,o,u,a;var f=e.length;if(t==-1){o=new Array(0);for(;;){u=new Array(o.length+1);for(i=0;i<o.length;i++)u[i+1]=o[i];u[0]=parseInt(e,10);o=u;r=e.indexOf(",",0);if(r<1)break;e=e.substring(r+1);if(e.length==0)break}if(o.length<n){u=new Array(n);copy_(u,o);return u}return o}o=int2bigInt(0,t*f,0);for(i=0;i<f;i++){r=digitsStr.indexOf(e.substring(i,i+1),0);if(t<=36&&r>=36)r-=26;if(r>=t||r<0){break}multInt_(o,t);addInt_(o,r)}for(f=o.length;f>0&&!o[f-1];f--);f=n>f+1?n:f+1;u=new Array(f);a=f<o.length?f:o.length;for(i=0;i<a;i++)u[i]=o[i];for(;i<f;i++)u[i]=0;return u}function equalsInt(e,t){var n;if(e[0]!=t)return 0;for(n=1;n<e.length;n++)if(e[n])return 0;return 1}function equals(e,t){var n;var r=e.length<t.length?e.length:t.length;for(n=0;n<r;n++)if(e[n]!=t[n])return 0;if(e.length>t.length){for(;n<e.length;n++)if(e[n])return 0}else{for(;n<t.length;n++)if(t[n])return 0}return 1}function isZero(e){var t;for(t=0;t<e.length;t++)if(e[t])return 0;return 1}function bigInt2str(e,t){var n,r,i="";if(s6.length!=e.length)s6=dup(e);else copy_(s6,e);if(t==-1){for(n=e.length-1;n>0;n--)i+=e[n]+",";i+=e[0]}else{while(!isZero(s6)){r=divInt_(s6,t);i=digitsStr.substring(r,r+1)+i}}if(i.length==0)i="0";return i}function dup(e){var t;buff=new Array(e.length);copy_(buff,e);return buff}function copy_(e,t){var n;var r=e.length<t.length?e.length:t.length;for(n=0;n<r;n++)e[n]=t[n];for(n=r;n<e.length;n++)e[n]=0}function copyInt_(e,t){var n,r;for(r=t,n=0;n<e.length;n++){e[n]=r&mask;r>>=bpe}}function addInt_(e,t){var n,r,i,s;e[0]+=t;r=e.length;i=0;for(n=0;n<r;n++){i+=e[n];s=0;if(i<0){s=-(i>>bpe);i+=s*radix}e[n]=i&mask;i=(i>>bpe)-s;if(!i)return}}function rightShift_(e,t){var n;var r=Math.floor(t/bpe);if(r){for(n=0;n<e.length-r;n++)e[n]=e[n+r];for(;n<e.length;n++)e[n]=0;t%=bpe}for(n=0;n<e.length-1;n++){e[n]=mask&(e[n+1]<<bpe-t|e[n]>>t)}e[n]>>=t}function halve_(e){var t;for(t=0;t<e.length-1;t++){e[t]=mask&(e[t+1]<<bpe-1|e[t]>>1)}e[t]=e[t]>>1|e[t]&radix>>1}function leftShift_(e,t){var n;var r=Math.floor(t/bpe);if(r){for(n=e.length;n>=r;n--)e[n]=e[n-r];for(;n>=0;n--)e[n]=0;t%=bpe}if(!t)return;for(n=e.length-1;n>0;n--){e[n]=mask&(e[n]<<t|e[n-1]>>bpe-t)}e[n]=mask&e[n]<<t}function multInt_(e,t){var n,r,i,s;if(!t)return;r=e.length;i=0;for(n=0;n<r;n++){i+=e[n]*t;s=0;if(i<0){s=-(i>>bpe);i+=s*radix}e[n]=i&mask;i=(i>>bpe)-s}}function divInt_(e,t){var n,r=0,i;for(n=e.length-1;n>=0;n--){i=r*radix+e[n];e[n]=Math.floor(i/t);r=i%t}return r}function linComb_(e,t,n,r){var i,s,o,u;o=e.length<t.length?e.length:t.length;u=e.length;for(s=0,i=0;i<o;i++){s+=n*e[i]+r*t[i];e[i]=s&mask;s>>=bpe}for(i=o;i<u;i++){s+=n*e[i];e[i]=s&mask;s>>=bpe}}function linCombShift_(e,t,n,r){var i,s,o,u;o=e.length<r+t.length?e.length:r+t.length;u=e.length;for(s=0,i=r;i<o;i++){s+=e[i]+n*t[i-r];e[i]=s&mask;s>>=bpe}for(i=o;s&&i<u;i++){s+=e[i];e[i]=s&mask;s>>=bpe}}function addShift_(e,t,n){var r,i,s,o;s=e.length<n+t.length?e.length:n+t.length;o=e.length;for(i=0,r=n;r<s;r++){i+=e[r]+t[r-n];e[r]=i&mask;i>>=bpe}for(r=s;i&&r<o;r++){i+=e[r];e[r]=i&mask;i>>=bpe}}function subShift_(e,t,n){var r,i,s,o;s=e.length<n+t.length?e.length:n+t.length;o=e.length;for(i=0,r=n;r<s;r++){i+=e[r]-t[r-n];e[r]=i&mask;i>>=bpe}for(r=s;i&&r<o;r++){i+=e[r];e[r]=i&mask;i>>=bpe}}function sub_(e,t){var n,r,i,s;i=e.length<t.length?e.length:t.length;for(r=0,n=0;n<i;n++){r+=e[n]-t[n];e[n]=r&mask;r>>=bpe}for(n=i;r&&n<e.length;n++){r+=e[n];e[n]=r&mask;r>>=bpe}}function add_(e,t){var n,r,i,s;i=e.length<t.length?e.length:t.length;for(r=0,n=0;n<i;n++){r+=e[n]+t[n];e[n]=r&mask;r>>=bpe}for(n=i;r&&n<e.length;n++){r+=e[n];e[n]=r&mask;r>>=bpe}}function mult_(e,t){var n;if(ss.length!=2*e.length)ss=new Array(2*e.length);copyInt_(ss,0);for(n=0;n<t.length;n++)if(t[n])linCombShift_(ss,e,t[n],n);copy_(e,ss)}function mod_(e,t){if(s4.length!=e.length)s4=dup(e);else copy_(s4,e);if(s5.length!=e.length)s5=dup(e);divide_(s4,t,s5,e)}function multMod_(e,t,n){var r;if(s0.length!=2*e.length)s0=new Array(2*e.length);copyInt_(s0,0);for(r=0;r<t.length;r++)if(t[r])linCombShift_(s0,e,t[r],r);mod_(s0,n);copy_(e,s0)}function squareMod_(e,t){var n,r,i,s,o,u,a;for(o=e.length;o>0&&!e[o-1];o--);a=o>t.length?2*o:2*t.length;if(s0.length!=a)s0=new Array(a);copyInt_(s0,0);for(n=0;n<o;n++){s=s0[2*n]+e[n]*e[n];s0[2*n]=s&mask;s>>=bpe;for(r=n+1;r<o;r++){s=s0[n+r]+2*e[n]*e[r]+s;s0[n+r]=s&mask;s>>=bpe}s0[n+o]=s}mod_(s0,t);copy_(e,s0)}function trim(e,t){var n,r;for(n=e.length;n>0&&!e[n-1];n--);r=new Array(n+t);copy_(r,e);return r}function powMod_(e,t,n){var r,i,s,o;if(s7.length!=n.length)s7=dup(n);if((n[0]&1)==0){copy_(s7,e);copyInt_(e,1);while(!equalsInt(t,0)){if(t[0]&1)multMod_(e,s7,n);divInt_(t,2);squareMod_(s7,n)}return}copyInt_(s7,0);for(s=n.length;s>0&&!n[s-1];s--);o=radix-inverseModInt(modInt(n,radix),radix);s7[s]=1;multMod_(e,s7,n);if(s3.length!=e.length)s3=dup(e);else copy_(s3,e);for(r=t.length-1;r>0&!t[r];r--);if(t[r]==0){copyInt_(e,1);return}for(i=1<<bpe-1;i&&!(t[r]&i);i>>=1);for(;;){if(!(i>>=1)){r--;if(r<0){mont_(e,one,n,o);return}i=1<<bpe-1}mont_(e,e,n,o);if(i&t[r])mont_(e,s3,n,o)}}function mont_(e,t,n,r){var i,s,o,u,a,f;var l=n.length;var c=t.length;if(sa.length!=l)sa=new Array(l);copyInt_(sa,0);for(;l>0&&n[l-1]==0;l--);for(;c>0&&t[c-1]==0;c--);f=sa.length-1;for(i=0;i<l;i++){a=sa[0]+e[i]*t[0];u=(a&mask)*r&mask;o=a+u*n[0]>>bpe;a=e[i];s=1;for(;s<c-4;){o+=sa[s]+u*n[s]+a*t[s];sa[s-1]=o&mask;o>>=bpe;s++;o+=sa[s]+u*n[s]+a*t[s];sa[s-1]=o&mask;o>>=bpe;s++;o+=sa[s]+u*n[s]+a*t[s];sa[s-1]=o&mask;o>>=bpe;s++;o+=sa[s]+u*n[s]+a*t[s];sa[s-1]=o&mask;o>>=bpe;s++;o+=sa[s]+u*n[s]+a*t[s];sa[s-1]=o&mask;o>>=bpe;s++}for(;s<c;){o+=sa[s]+u*n[s]+a*t[s];sa[s-1]=o&mask;o>>=bpe;s++}for(;s<l-4;){o+=sa[s]+u*n[s];sa[s-1]=o&mask;o>>=bpe;s++;o+=sa[s]+u*n[s];sa[s-1]=o&mask;o>>=bpe;s++;o+=sa[s]+u*n[s];sa[s-1]=o&mask;o>>=bpe;s++;o+=sa[s]+u*n[s];sa[s-1]=o&mask;o>>=bpe;s++;o+=sa[s]+u*n[s];sa[s-1]=o&mask;o>>=bpe;s++}for(;s<l;){o+=sa[s]+u*n[s];sa[s-1]=o&mask;o>>=bpe;s++}for(;s<f;){o+=sa[s];sa[s-1]=o&mask;o>>=bpe;s++}sa[s-1]=o&mask}if(!greater(n,sa))sub_(sa,n);copy_(e,sa)}bpe=0;mask=0;radix=mask+1;digitsStr="0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_=!@#$%^&*()[]{}|;:,.<>/?`~ \\'\"+-";for(bpe=0;1<<bpe+1>1<<bpe;bpe++);bpe>>=1;mask=(1<<bpe)-1;radix=mask+1;one=int2bigInt(1,1,1);t=new Array(0);ss=t;s0=t;s1=t;s2=t;s3=t;s4=t;s5=t;s6=t;s7=t;T=t;sa=t;mr_x1=t;mr_r=t;mr_a=t;eg_v=t;eg_u=t;eg_A=t;eg_B=t;eg_C=t;eg_D=t;md_q1=t;md_q2=t;md_q3=t;md_r=t;md_r1=t;md_r2=t;md_tt=t;primes=t;pows=t;s_i=t;s_i2=t;s_R=t;s_rm=t;s_q=t;s_n1=t;s_a=t;s_r2=t;s_n=t;s_b=t;s_d=t;s_x1=t;s_x2=t,s_aa=t;rpprb=t