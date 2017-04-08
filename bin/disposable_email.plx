#!/usr/bin/perl

use strict;
use warnings;

use LWP::UserAgent;
use Regexp::Assemble;

my $ua = LWP::UserAgent->new(ssl_opts => { verify_hostname => 1 });
my $list1 = $ua->get("https://raw.githubusercontent.com/wesbos/burner-email-providers/master/emails.txt");
my $list2 = $ua->get("https://raw.githubusercontent.com/martenson/disposable-email-domains/master/disposable_email_blacklist.conf");

die "Could not download lists" unless ($list1->is_success && $list2->is_success);

my %union = ();

foreach(split("\n", $list1->decoded_content), split("\n", $list2->decoded_content)) {
    $union{$_} = 1;
}

my $regex = Regexp::Assemble->new;
foreach my $domain (keys %union) {
	$domain =~ s/\./\\./g;
	$domain = "$domain\$";
	$regex->add($domain);
}

print '/@';
print substr($regex->re, 5, -1);
print '/';
