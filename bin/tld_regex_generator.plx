#!/usr/bin/env perl

use strict;
use warnings;

use LWP::UserAgent;
use Regexp::Assemble;

my $ua = LWP::UserAgent->new(ssl_opts => { verify_hostname => 1 });
my $res = $ua->get("https://data.iana.org/TLD/tlds-alpha-by-domain.txt");

die "Downloading lists failed." unless ($res->is_success);

my @tlds = split("\n", $res->content);
my $regex = Regexp::Assemble->new;

foreach my $tld (@tlds) {
	if ($tld !~ /^(?:#.*|\s+)?$/ && $tld ne "ARPA") {
		$tld = lc($tld);
		$regex->add("^$tld\$");
	}
}

print '/';
print substr($regex->re, 4, -1);
print '/';
