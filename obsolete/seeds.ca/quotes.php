<?

function SoD_Quote( $lang = "EN" ) {
    global $SoD_Quotes_EN, $SoD_Quotes_FR;

if( $lang != "EN" ) return( array("","") );


    if( $lang == "EN" )  $q =& $SoD_Quotes_EN;      // this is the only way that I could conditionally set a reference
    else                 $q =& $SoD_Quotes_FR;      // variable.  didn't want to copy it
    $i = array_rand($q);
    return( $q[$i] );
}



$SoD_Quotes_EN = array (

// Gardens
array( "Everything that slows us down and forces patience, everything that sets us back into the slow cycles of nature, is a help.  Gardening is an instrument of grace.",
       "May Sarton" ),
array( "If there's one thing I can say about my garden, it can always surprise me.",
       "David Hobson" ),
array( "If you want to be happy for a lifetime, plant a garden.",
       "Chinese Proverb" ),
array( "Gardening is a way of showing that you believe in tomorrow.",
       "Anonymous" ),
array( "Gardening is the only unquestionably useful job.",
       "George Bernard Shaw" ),
array( "There can be no other occupation like gardening in which, if you were to creep up behind someone at their work, you would find them smiling.",
       "Mirabel Osler" ),
array( "When I go into my garden with a spade, and dig a bed, I feel such an exhilaration and health that I discover that I have been defrauding myself all this time in letting others do for me what I should have done with my own hands.",
       "Ralph Waldo Emerson" ),
array( "No occupation is so delightful to me as the culture of the earth, no culture comparable to that of the garden... But though an old man, I am but a young gardener.",
       "Thomas Jefferson" ),
array( "The best fertilizer is the gardener's shadow.",
       "Anonymous" ),


// Jokes
array( "Q: What's the first thing you should put in your garden in the spring?  A: Your foot",
       "" ),
array( "Q: Why did the strawberry call 911?  A: It was in a jam",
       "" ),

// Flowers
array( "Art is the unceasing effort to compete with the beauty of flowers  and never succeed.",
       "Marc Chagall" ),
array( "The love of flowers is really the best teacher of how to grow and understand them.",
       "Max Schling" ),
array( "All the flowers of all the tomorrows are in the seeds of today.",
       "Indian Proverb" ),


// Fruit
array( "The sun, with all those planets revolving around it and dependent on it, can still ripen a bunch of grapes as if it had nothing else in the universe to do.",
       "Galileo" ),
array( "In an orchard there should be enough to eat, enough to lay up, enough to be stolen, and enough to rot on the ground.",
       "James Boswell, 1740-1795" ),
array( "Love is a fruit in season at all times, and within reach of every hand.",
       "Mother Teresa" ),
array( "Wishing to be friends is quick work, but friendship is a slow ripening fruit.",
       "Aristotle" ),
array( "Thought is the blossom, language the bud, action the fruit behind.",
       "Ralph Waldo Emerson" ),
array( "Like a prune, you are not getting any better looking, but you are getting sweeter.",
       "N. D. Stice" ),
array( "He that would have fruit must climb the tree.",
       "Thomas Fuller" ),
array( "Fruit should pay for the welfare of leaves.",
       "Yiddish Proverb" ),
array( "The true meaning of life is to plant trees, under whose shade you do not expect to sit.",
       "Nelson Henderson" ),
array( "The nut doesn't reveal the tree it contains.",
       "Ancient Egyptian Proverb" ),
array( "Do not be afraid to go out on a limb... That's where the fruit is.",
       "Anonymous" ),
array( "There is peace in the garden.  Peace and results.",
       "Ruth Stout" ),
array( "Anyone can count the number of seeds in an apple, but only God can count the number of apples in a seed.",
       "Robert H. Schuller" ),
array( "Fast Ripe, Fast Rotten.",
       "Japanese Proverb" ),
array( "When life hands you a lemon, say, 'Oh yeah, I like lemons. What else ya got?'",
       "Henry Rollins" ),
array( "Words are like leaves; and where they most abound; Much fruit of sense beneath is rarely found.",
       "Alexander Pope, 1688-1744" ),
array( "It was not a watermelon that Eve took; we know it because she repented.",
       "Mark Twain" ),
array( "Don't shake the tree when the pears fall off themselves.",
       "Slovakian Proverb" ),
array( "In this sequestered nook how sweet; To sit upon my orchard seat; And birds and flowers once more to greet",
       "William Wordsworth" ),

// Weeds
array( "And so it criticized each flower, This supercilious seed; Until it woke one summer hour, And found itself a weed.",
       "Mildred Howells" ),
array( "A good garden may have some weeds.",
       "Anonymous" ),
array( "A weed is simply a plant that wants to grow where people want something else.",
       "Anonymous" ),
array( "My idea of gardening is to discover something wild in my wood and weed around it with the utmost care until it has a chance to grow and spread.",
       "Margaret Bourke-White" ),
array( "If you are a garden plant you are well regarded just as long as you stay in the garden.",
       "Davies Gilbert" ),
array( "A weed is a plant that is not only in the wrong place, but intends to stay.",
       "Sara Stein" ),
array( "I consider every plant hardy until I have killed it myself.",
       "Sir Peter Smithers" ),
array( "A flower is an educated weed.",
       "Luther Burbank" ),
array( "What is a weed?  A plant whose virtues have not yet been discovered.",
       "Ralph Waldo Emerson" ),
array( "Sweet flowers are slow and weeds make haste.",
       "William Shakespeare" ),
array( "Gardening is a kind of disease.   It infects you, you cannot escape it.  When you go visiting, your eyes rove about the garden; you interrupt the serious cocktail drinking because of an irresistible impulse to get up and pull a weed.",
       "Lewis Gannit" ),
array( "The prayer of the farmer kneeling in his field to weed it, the prayer of the rower kneeling with the stroke of his oar, are true prayers heard throughout nature.",
       "Ralph Waldo Emerson" ),
array( "Weeds are flowers too, once you get to know them",
       "A. A. Milne,  Eeyore from Winnie the Pooh" ),
array( "A weed is a plant that has mastered every survival skill except for learning how to grow in rows.",
       "Doug Larson" ),
array( "My basic weeding rule: if they grow in rows they're flowers; if they don't they're weeds.",
       "David Hobson" ),
array( "If dandelions were hard to grow, they would be most welcome on any lawn.",
       "Andrew V. Mason" ),
array( "Plant and your spouse plants with you; weed and you weed alone.",
       "Dennis Breeze" ),
array( "If a person cannot love a plant after he has pruned it, then he has either done a poor job or is devoid of emotion.",
       "Liberty Hyde Bailey" ),
array( "The philosopher who said that work well done never needs doing over never weeded a garden.",
       "Ray D. Everson" ),
array( "Weeds are the little vices that beset plant life, and are to be got rid of the best way we know how.",
       "Farmer's Almanac, 1881" ),
array( "The secret to good farming is to leave the land better than you found it.",
       "George Henderson" ),


array( "Without gardening, life would be a mistake",
       "Anonymous" ),    // actually paraphrased from Nietzsche: Without music, life would be a mistake.
array( "Variety is the spice of life",
       "Anonymous" )
);




$SoD_Quotes_FR = array (

array( "Tout ce qui nous ralentit et nous contraint à la patience, tout ce qui nous ramène aux cycles de la nature est un bienfait.  Le jardinage est une grâce.",
       "May Sarton" ),
);

?>
